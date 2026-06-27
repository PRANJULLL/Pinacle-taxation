<?php
// api/tasks.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Helper to generate unique Order ID
function generateOrderId() {
    $timestamp = strtoupper(base_convert(time(), 10, 36));
    $random = strtoupper(substr(md5(mt_rand()), 0, 4));
    return "ORD-" . $timestamp . "-" . $random;
}

// Plan to price mapping
$PLAN_PRICING = [
    'Basic' => 500,
    'Elite' => 1800,
    'Elite RSU' => 2000,
    'Premium' => 1300,
    'Assisted Filing - Basic' => 500,
    'Assisted Filing - Premium' => 1300,
    'Assisted Filing - Elite' => 1800
];

try {
    if ($method === 'GET') {
        // -------------------------------------------------------------
        // READ: Fetch task list with filters, sorting, and pagination
        // -------------------------------------------------------------
        
        // Check if fetching a single task by ID
        $taskId = isset($_GET['id']) ? $_GET['id'] : '';
        if ($taskId !== '') {
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            if (!$task) {
                http_response_code(404);
                echo json_encode(['message' => 'Task not found']);
                exit;
            }
            echo json_encode($task);
            exit;
        }

        // Query parameters
        $client = isset($_GET['client']) ? $_GET['client'] : 'All';
        $status = isset($_GET['status']) ? $_GET['status'] : 'All';
        $employee = isset($_GET['employee']) ? $_GET['employee'] : 'All';
        $plan = isset($_GET['plan']) ? $_GET['plan'] : 'All';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $dateFilter = isset($_GET['dateFilter']) ? $_GET['dateFilter'] : 'All';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'createdAt';
        $sortOrder = isset($_GET['sortOrder']) && strtolower($_GET['sortOrder']) === 'asc' ? 'ASC' : 'DESC';

        // Validate page and limit
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        $offset = ($page - 1) * $limit;

        // Build SQL Where Clauses
        $whereClauses = ["1=1"];
        $sqlParams = [];

        if ($client !== 'All') {
            $whereClauses[] = "client = :client";
            $sqlParams['client'] = $client;
        }

        if ($status !== 'All') {
            if ($status === 'Pending') {
                $whereClauses[] = "status IN ('Pending', 'Stuck')";
            } else {
                $whereClauses[] = "status = :status";
                $sqlParams['status'] = $status;
            }
        }

        if ($employee !== 'All') {
            $whereClauses[] = "taxExpert = :employee";
            $sqlParams['employee'] = $employee;
        }

        if ($plan !== 'All') {
            // Match plans with or without prefix
            $planMap = [
                'Basic' => ['Basic', 'Assisted Filing - Basic'],
                'Premium' => ['Premium', 'Assisted Filing - Premium'],
                'Elite' => ['Elite', 'Assisted Filing - Elite', 'Elite RSU'],
                'Elite RSU' => ['Elite RSU']
            ];
            $mappedPlans = isset($planMap[$plan]) ? $planMap[$plan] : [$plan];
            
            $placeholders = [];
            foreach ($mappedPlans as $idx => $p) {
                $key = "plan_" . $idx;
                $placeholders[] = ":" . $key;
                $sqlParams[$key] = $p;
            }
            $whereClauses[] = "plan IN (" . implode(', ', $placeholders) . ")";
        }

        if ($search !== '') {
            $whereClauses[] = "(customerName LIKE :search 
                               OR pan LIKE :search 
                               OR phone LIKE :search 
                               OR email LIKE :search 
                               OR orderId LIKE :search 
                               OR taxExpert LIKE :search)";
            $sqlParams['search'] = "%" . $search . "%";
        }

        if ($dateFilter !== 'All') {
            $now = time();
            if ($dateFilter === 'Today') {
                $start = date('Y-m-d 00:00:00');
                $whereClauses[] = "createdAt >= :dateStart";
                $sqlParams['dateStart'] = $start;
            } elseif ($dateFilter === 'This Week') {
                // Start of week (Sunday)
                $dayOfWeek = date('w');
                $start = date('Y-m-d 00:00:00', strtotime("-$dayOfWeek days"));
                $whereClauses[] = "createdAt >= :dateStart";
                $sqlParams['dateStart'] = $start;
            } elseif ($dateFilter === 'This Month') {
                $start = date('Y-m-01 00:00:00');
                $whereClauses[] = "createdAt >= :dateStart";
                $sqlParams['dateStart'] = $start;
            }
        }

        $whereSQL = implode(' AND ', $whereClauses);

        // Allowed sorting fields
        $allowedSorts = ['createdAt', 'customerName', 'orderId', 'amount', 'status', 'taxExpert', 'completedAt'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'createdAt';
        }

        // Total Count Query
        $countQuery = "SELECT COUNT(*) FROM tasks WHERE " . $whereSQL;
        $countStmt = $pdo->prepare($countQuery);
        // Bind parameters
        foreach ($sqlParams as $key => $val) {
            $countStmt->bindValue(':' . $key, $val);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Data Query
        $dataQuery = "SELECT * FROM tasks WHERE " . $whereSQL . " ORDER BY " . $sortBy . " " . $sortOrder . " LIMIT :limit OFFSET :offset";
        $dataStmt = $pdo->prepare($dataQuery);
        // Bind parameters
        foreach ($sqlParams as $key => $val) {
            $dataStmt->bindValue(':' . $key, $val);
        }
        $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->execute();
        $tasks = $dataStmt->fetchAll();

        // Format data numbers
        foreach ($tasks as &$task) {
            $task['amount'] = (float)$task['amount'];
        }

        echo json_encode([
            'tasks' => $tasks,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
        exit;
    } 
    
    elseif ($method === 'POST') {
        // -------------------------------------------------------------
        // CREATE: Add new filing task
        // -------------------------------------------------------------
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid input data']);
            exit;
        }

        // Required fields
        $required = ['client', 'customerName', 'taxExpert'];
        if (isset($data['client']) && $data['client'] === 'Clear Tax') {
            $required[] = 'phone';
            $required[] = 'email';
            $required[] = 'plan';
        }
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['message' => "Field '$field' is required"]);
                exit;
            }
        }

        $client = $data['client'];
        $customerName = $data['customerName'];
        $phone = isset($data['phone']) ? trim($data['phone']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $plan = isset($data['plan']) ? $data['plan'] : '';
        $taxExpert = $data['taxExpert'];
        $pan = isset($data['pan']) ? trim($data['pan']) : '';
        $remarks = isset($data['remarks']) ? trim($data['remarks']) : '';
        $reference = isset($data['reference']) ? trim($data['reference']) : '';
        $createdAt = !empty($data['createdAt']) ? date('Y-m-d H:i:s', strtotime($data['createdAt'])) : date('Y-m-d H:i:s');
        
        // Calculate amount
        $amount = 0;
        if ($client === 'Clear Tax') {
            if (!isset($PLAN_PRICING[$plan])) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid plan selected for Clear Tax']);
                exit;
            }
            $amount = $PLAN_PRICING[$plan];
        } else {
            $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
        }

        // Calculate Order ID
        $orderId = isset($data['orderId']) ? trim($data['orderId']) : '';
        if ($client !== 'Clear Tax' || empty($orderId)) {
            $orderId = generateOrderId();
        } else {
            // For Clear Tax, verify unique Order ID
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE orderId = ?");
            $stmt->execute([$orderId]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(400);
                echo json_encode(['message' => "Order ID '$orderId' already exists"]);
                exit;
            }
        }

        // Insert
        $sql = "INSERT INTO tasks (orderId, client, customerName, pan, phone, email, plan, amount, taxExpert, remarks, reference, status, createdAt) 
                VALUES (:orderId, :client, :customerName, :pan, :phone, :email, :plan, :amount, :taxExpert, :remarks, :reference, 'Pending', :createdAt)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'orderId' => $orderId,
            'client' => $client,
            'customerName' => $customerName,
            'pan' => $pan,
            'phone' => $phone,
            'email' => $email,
            'plan' => $plan,
            'amount' => $amount,
            'taxExpert' => $taxExpert,
            'remarks' => $remarks,
            'reference' => $reference,
            'createdAt' => $createdAt
        ]);

        $newId = $pdo->lastInsertId();
        
        // Return new task details
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$newId]);
        $newTask = $stmt->fetch();
        $newTask['amount'] = (float)$newTask['amount'];

        http_response_code(201);
        echo json_encode($newTask);
        exit;
    } 
    
    elseif ($method === 'PUT') {
        // -------------------------------------------------------------
        // UPDATE: Modify active filing task details
        // -------------------------------------------------------------
        $taskId = isset($_GET['id']) ? $_GET['id'] : '';
        if ($taskId === '') {
            http_response_code(400);
            echo json_encode(['message' => 'Task ID is required for update']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid input data']);
            exit;
        }

        // Check if task exists
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $existingTask = $stmt->fetch();
        if (!$existingTask) {
            http_response_code(404);
            echo json_encode(['message' => 'Task not found']);
            exit;
        }

        // Prepare updates mapping
        $fields = ['client', 'customerName', 'pan', 'phone', 'email', 'plan', 'taxExpert', 'remarks', 'reference', 'status', 'createdAt'];
        $updateSQL = [];
        $updateParams = ['id' => $taskId];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $val = $data[$field];
                
                // Extra logic for status dates
                if ($field === 'status') {
                    if ($val === 'Completed' && $existingTask['status'] !== 'Completed') {
                        $updateSQL[] = "completedAt = :completedAt";
                        $updateParams['completedAt'] = date('Y-m-d H:i:s');
                    } elseif (($val === 'Pending' || $val === 'Stuck') && $existingTask['status'] === 'Completed') {
                        $updateSQL[] = "completedAt = :completedAt";
                        $updateParams['completedAt'] = null;
                    }
                }

                if ($field === 'createdAt') {
                    $val = date('Y-m-d H:i:s', strtotime($val));
                }

                $updateSQL[] = "$field = :$field";
                $updateParams[$field] = $val;
            }
        }

        // If client switches to Clear Tax, automatically update plan pricing
        $newClient = isset($data['client']) ? $data['client'] : $existingTask['client'];
        $newPlan = isset($data['plan']) ? $data['plan'] : $existingTask['plan'];
        
        if ($newClient === 'Clear Tax') {
            $updateSQL[] = "amount = :amount";
            $updateParams['amount'] = isset($PLAN_PRICING[$newPlan]) ? $PLAN_PRICING[$newPlan] : 0;
        } elseif (isset($data['amount'])) {
            $updateSQL[] = "amount = :amount";
            $updateParams['amount'] = (float)$data['amount'];
        }

        if (count($updateSQL) === 0) {
            echo json_encode($existingTask);
            exit;
        }

        $sql = "UPDATE tasks SET " . implode(', ', $updateSQL) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateParams);

        // Fetch updated
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $updatedTask = $stmt->fetch();
        $updatedTask['amount'] = (float)$updatedTask['amount'];

        echo json_encode($updatedTask);
        exit;
    } 
    
    elseif ($method === 'DELETE') {
        // -------------------------------------------------------------
        // DELETE: Remove filing task
        // -------------------------------------------------------------
        $taskId = isset($_GET['id']) ? $_GET['id'] : '';
        if ($taskId === '') {
            http_response_code(400);
            echo json_encode(['message' => 'Task ID is required for delete']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);

        echo json_encode(['message' => 'Task deleted successfully']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to process tasks', 'error' => $e->getMessage()]);
}
