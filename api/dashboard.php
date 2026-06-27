<?php
// api/dashboard.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'stats';
$client = isset($_GET['client']) ? $_GET['client'] : 'All';

// Build SQL filter for client
$clientFilter = "";
$params = [];
if ($client !== 'All') {
    $clientFilter = " AND client = :client";
    $params['client'] = $client;
}

try {
    if ($action === 'stats') {
        // Today range
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        // Total
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE 1=1" . $clientFilter);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Pending
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'" . $clientFilter);
        $stmt->execute($params);
        $pending = $stmt->fetchColumn();

        // Completed
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'" . $clientFilter);
        $stmt->execute($params);
        $completed = $stmt->fetchColumn();

        // Stuck
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Stuck'" . $clientFilter);
        $stmt->execute($params);
        $stuck = $stmt->fetchColumn();

        // Today created
        $todayParams = $params;
        $todayParams['start'] = $todayStart;
        $todayParams['end'] = $todayEnd;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE createdAt >= :start AND createdAt <= :end" . $clientFilter);
        $stmt->execute($todayParams);
        $todayTasks = $stmt->fetchColumn();

        // Today completed
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Completed' AND completedAt >= :start AND completedAt <= :end" . $clientFilter);
        $stmt->execute($todayParams);
        $todayCompleted = $stmt->fetchColumn();

        echo json_encode([
            'total' => (int)$total,
            'pending' => (int)$pending,
            'completed' => (int)$completed,
            'stuck' => (int)$stuck,
            'todayTasks' => (int)$todayTasks,
            'todayCompleted' => (int)$todayCompleted
        ]);
        exit;
    } elseif ($action === 'charts') {
        // 1. Tasks by employee
        $stmt = $pdo->prepare("SELECT taxExpert as name, COUNT(*) as value FROM tasks WHERE 1=1" . $clientFilter . " GROUP BY taxExpert ORDER BY value DESC");
        $stmt->execute($params);
        $tasksByEmployee = $stmt->fetchAll();

        // 2. Tasks by client
        $stmt = $pdo->prepare("SELECT client as name, COUNT(*) as value FROM tasks WHERE 1=1" . $clientFilter . " GROUP BY client");
        $stmt->execute($params);
        $tasksByClient = $stmt->fetchAll();

        // 3. Revenue by client (only completed tasks)
        $stmt = $pdo->prepare("SELECT client as name, SUM(amount) as value FROM tasks WHERE status = 'Completed'" . $clientFilter . " GROUP BY client");
        $stmt->execute($params);
        $revenueByClient = $stmt->fetchAll();
        foreach ($revenueByClient as &$r) {
            $r['value'] = (float)$r['value'];
        }

        // 4. Status distribution
        $stmt = $pdo->prepare("SELECT status as name, COUNT(*) as value FROM tasks WHERE 1=1" . $clientFilter . " GROUP BY status");
        $stmt->execute($params);
        $statusDistribution = $stmt->fetchAll();

        // 5. Monthly Revenue (last 6 months, only completed tasks)
        // Group by year and month using completedAt date
        $sixMonthsAgo = date('Y-m-d 00:00:00', strtotime('-5 months first day of this month'));
        
        $monthlyParams = $params;
        $monthlyParams['sixMonthsAgo'] = $sixMonthsAgo;
        
        $sql = "SELECT 
                    DATE_FORMAT(completedAt, '%Y-%m') as name, 
                    SUM(amount) as value 
                FROM tasks 
                WHERE status = 'Completed' AND completedAt >= :sixMonthsAgo" . $clientFilter . " 
                GROUP BY DATE_FORMAT(completedAt, '%Y-%m') 
                ORDER BY name ASC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($monthlyParams);
        $rawMonthly = $stmt->fetchAll();

        // Initialize monthly array with past 6 months to ensure we show zero revenue months
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStr = date('Y-m', strtotime("-$i months"));
            $val = 0;
            foreach ($rawMonthly as $item) {
                if ($item['name'] === $monthStr) {
                    $val = (float)$item['value'];
                    break;
                }
            }
            $monthlyRevenue[] = [
                'name' => $monthStr,
                'value' => $val
            ];
        }

        echo json_encode([
            'tasksByEmployee' => $tasksByEmployee,
            'tasksByClient' => $tasksByClient,
            'revenueByClient' => $revenueByClient,
            'statusDistribution' => $statusDistribution,
            'monthlyRevenue' => $monthlyRevenue
        ]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch dashboard data', 'error' => $e->getMessage()]);
}
