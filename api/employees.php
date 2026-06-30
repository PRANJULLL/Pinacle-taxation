<?php
// api/employees.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$employeeName = isset($_GET['employee']) ? $_GET['employee'] : '';

try {
    if ($employeeName !== '') {
        // 1. Specific Employee Stats & Tasks List
        // Fetch employee
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE name = ?");
        $stmt->execute([$employeeName]);
        $emp = $stmt->fetch();
        if (!$emp) {
            http_response_code(404);
            echo json_encode(['message' => 'Employee not found']);
            exit;
        }

        // Fetch their tasks
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE taxExpert = ? ORDER BY createdAt DESC");
        $stmt->execute([$employeeName]);
        $tasks = $stmt->fetchAll();

        // Calculate stats
        $total = count($tasks);
        $pending = 0;
        $completed = 0;
        $stuck = 0;
        $totalHours = 0;
        $completedCountWithDates = 0;

        foreach ($tasks as $t) {
            if ($t['status'] === 'Pending') $pending++;
            elseif ($t['status'] === 'Completed' || $t['status'] === 'Transaction Completed') $completed++;
            elseif ($t['status'] === 'Stuck') $stuck++;

            if (($t['status'] === 'Completed' || $t['status'] === 'Transaction Completed') && !empty($t['completedAt'])) {
                $created = strtotime($t['createdAt']);
                $completedTime = strtotime($t['completedAt']);
                if ($completedTime > $created) {
                    $totalHours += ($completedTime - $created) / 3600;
                    $completedCountWithDates++;
                }
            }
        }

        $avgCompletionHours = 0;
        if ($completedCountWithDates > 0) {
            $avgCompletionHours = round($totalHours / $completedCountWithDates, 1);
        }

        echo json_encode([
            'name' => $employeeName,
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
            'stuck' => $stuck,
            'avgCompletionHours' => $avgCompletionHours,
            'tasks' => $tasks
        ]);
        exit;
    } elseif ($action === 'stats') {
        // 2. All Employees Workload Stats
        $subFilter = isset($_GET['subFilter']) ? $_GET['subFilter'] : 'All';
        
        $clientFilter = "";
        if ($subFilter === 'Pinnacle_Vishnu') {
            $clientFilter = " AND client IN ('Pinnacle', 'Vishnu')";
        } elseif ($subFilter === 'Clear_Tax') {
            $clientFilter = " AND client = 'Clear Tax'";
        }

        $stmt = $pdo->query("SELECT name FROM employees ORDER BY name ASC");
        $employees = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stats = [];
        foreach ($employees as $name) {
            $stmt = $pdo->prepare("SELECT status, createdAt, completedAt FROM tasks WHERE taxExpert = ?" . $clientFilter);
            $stmt->execute([$name]);
            $tasks = $stmt->fetchAll();
            
            $total = count($tasks);
            $pending = 0;
            $completed = 0;
            $stuck = 0;
            $totalHours = 0;
            $completedCountWithDates = 0;

            foreach ($tasks as $t) {
                if ($t['status'] === 'Pending') $pending++;
                elseif ($t['status'] === 'Completed' || $t['status'] === 'Transaction Completed') $completed++;
                elseif ($t['status'] === 'Stuck') $stuck++;

                if (($t['status'] === 'Completed' || $t['status'] === 'Transaction Completed') && !empty($t['completedAt'])) {
                    $created = strtotime($t['createdAt']);
                    $completedTime = strtotime($t['completedAt']);
                    if ($completedTime > $created) {
                        $totalHours += ($completedTime - $created) / 3600;
                        $completedCountWithDates++;
                    }
                }
            }

            $avgCompletionHours = 0;
            if ($completedCountWithDates > 0) {
                $avgCompletionHours = round($totalHours / $completedCountWithDates, 1);
            }

            $stats[] = [
                'name' => $name,
                'total' => $total,
                'pending' => $pending,
                'completed' => $completed,
                'stuck' => $stuck,
                'avgCompletionHours' => $avgCompletionHours
            ];
        }
        echo json_encode($stats);
        exit;
    } else {
        // 3. Simple List of Employees
        $stmt = $pdo->query("SELECT id, name FROM employees ORDER BY name ASC");
        $employees = $stmt->fetchAll();
        echo json_encode($employees);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to process request', 'error' => $e->getMessage()]);
}
