<?php
// api/reports.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../libs/fpdf/fpdf.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'stats';

try {
    if ($action === 'stats') {
        // -------------------------------------------------------------
        // GENERAL REPORT STATISTICS
        // -------------------------------------------------------------
        
        // Revenue (sum of completed tasks amount)
        $stmt = $pdo->query("SELECT SUM(amount) FROM tasks WHERE status = 'Completed'");
        $revenue = (float)$stmt->fetchColumn();

        // Count statuses
        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'");
        $completedTasks = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'");
        $pendingTasks = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Stuck'");
        $stuckTasks = (int)$stmt->fetchColumn();

        // Employee Performance statistics (now includes pending/stuck breakdown too)
        $sql = "SELECT 
                    taxExpert as name, 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Stuck' THEN 1 ELSE 0 END) as stuck,
                    SUM(CASE WHEN status = 'Completed' THEN amount ELSE 0 END) as revenue
                FROM tasks 
                GROUP BY taxExpert
                ORDER BY revenue DESC";
        $stmt = $pdo->query($sql);
        $employeePerformance = $stmt->fetchAll();
        foreach ($employeePerformance as &$emp) {
            $emp['total'] = (int)$emp['total'];
            $emp['completed'] = (int)$emp['completed'];
            $emp['pending'] = (int)$emp['pending'];
            $emp['stuck'] = (int)$emp['stuck'];
            $emp['revenue'] = (float)$emp['revenue'];
        }

        // Revenue & task breakdown by Source / Client (Pinnacle, Vishnu, Clear Tax)
        $sql = "SELECT 
                    client,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Stuck' THEN 1 ELSE 0 END) as stuck,
                    SUM(CASE WHEN status = 'Completed' THEN amount ELSE 0 END) as revenue
                FROM tasks
                GROUP BY client
                ORDER BY revenue DESC";
        $stmt = $pdo->query($sql);
        $revenueByClient = $stmt->fetchAll();
        foreach ($revenueByClient as &$src) {
            $src['total'] = (int)$src['total'];
            $src['completed'] = (int)$src['completed'];
            $src['pending'] = (int)$src['pending'];
            $src['stuck'] = (int)$src['stuck'];
            $src['revenue'] = (float)$src['revenue'];
        }

        // Revenue breakdown by Filing Plan (Basic/Premium/Elite/Elite RSU)
        $sql = "SELECT 
                    plan,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Completed' THEN amount ELSE 0 END) as revenue
                FROM tasks
                WHERE plan IS NOT NULL AND plan != ''
                GROUP BY plan
                ORDER BY revenue DESC";
        $stmt = $pdo->query($sql);
        $revenueByPlan = $stmt->fetchAll();
        foreach ($revenueByPlan as &$pl) {
            $pl['total'] = (int)$pl['total'];
            $pl['completed'] = (int)$pl['completed'];
            $pl['revenue'] = (float)$pl['revenue'];
        }

        echo json_encode([
            'revenue' => $revenue,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'stuckTasks' => $stuckTasks,
            'employeePerformance' => $employeePerformance,
            'revenueByClient' => $revenueByClient,
            'revenueByPlan' => $revenueByPlan
        ]);
        exit;
    } 
    
    elseif ($action === 'excel') {
        // -------------------------------------------------------------
        // EXPORT EXCEL (CSV STREAM WITH EXCEL COMPATIBLE HEADERS)
        // -------------------------------------------------------------
        
        // Query parameters
        $client = isset($_GET['client']) ? $_GET['client'] : 'All';
        $status = isset($_GET['status']) ? $_GET['status'] : 'All';
        $employee = isset($_GET['employee']) ? $_GET['employee'] : 'All';
        $plan = isset($_GET['plan']) ? $_GET['plan'] : 'All';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $dateFilter = isset($_GET['dateFilter']) ? $_GET['dateFilter'] : 'All';

        // Build SQL Where Clauses
        $whereClauses = ["1=1"];
        $sqlParams = [];

        if ($client !== 'All') {
            if ($client === 'Pinnacle_Vishnu') {
                $whereClauses[] = "client IN ('Pinnacle', 'Vishnu')";
            } else {
                $whereClauses[] = "client = :client";
                $sqlParams['client'] = $client;
            }
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
            $whereClauses[] = "(customerName LIKE :search1 
                               OR pan LIKE :search2 
                               OR phone LIKE :search3 
                               OR email LIKE :search4 
                               OR orderId LIKE :search5 
                               OR taxExpert LIKE :search6)";
            $searchTerm = "%" . $search . "%";
            $sqlParams['search1'] = $searchTerm;
            $sqlParams['search2'] = $searchTerm;
            $sqlParams['search3'] = $searchTerm;
            $sqlParams['search4'] = $searchTerm;
            $sqlParams['search5'] = $searchTerm;
            $sqlParams['search6'] = $searchTerm;
        }

        if ($dateFilter !== 'All') {
            if ($dateFilter === 'Today') {
                $start = date('Y-m-d 00:00:00');
                $whereClauses[] = "createdAt >= :dateStart";
                $sqlParams['dateStart'] = $start;
            } elseif ($dateFilter === 'This Week') {
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

        // Fetch filtered tasks
        $query = "SELECT * FROM tasks WHERE " . $whereSQL . " ORDER BY createdAt DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($sqlParams);
        $tasks = $stmt->fetchAll();

        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set response headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tasks-export.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add headers
        fputcsv($output, ['Order ID', 'Client', 'Customer Name', 'PAN', 'Phone', 'Email', 'Plan', 'Amount', 'Tax Expert', 'Status', 'Created At']);

        // Add task rows
        foreach ($tasks as $task) {
            fputcsv($output, [
                $task['orderId'],
                $task['client'],
                $task['customerName'],
                $task['pan'],
                $task['phone'],
                $task['email'],
                $task['plan'],
                (float)$task['amount'],
                $task['taxExpert'],
                $task['status'],
                $task['createdAt']
            ]);
        }
        fclose($output);
        exit;
    } 
    
    elseif ($action === 'pdf') {
        // -------------------------------------------------------------
        // EXPORT PDF REPORT USING FPDF
        // -------------------------------------------------------------
        
        // Fetch stats
        $stmt = $pdo->query("SELECT SUM(amount) FROM tasks WHERE status = 'Completed'");
        $revenue = (float)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'");
        $completedTasks = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'");
        $pendingTasks = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Stuck'");
        $stuckTasks = (int)$stmt->fetchColumn();

        $sql = "SELECT 
                    taxExpert as name, 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Stuck' THEN 1 ELSE 0 END) as stuck,
                    SUM(CASE WHEN status = 'Completed' THEN amount ELSE 0 END) as revenue
                FROM tasks 
                GROUP BY taxExpert
                ORDER BY revenue DESC";
        $stmt = $pdo->query($sql);
        $employeePerformance = $stmt->fetchAll();

        $sql = "SELECT 
                    client,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Stuck' THEN 1 ELSE 0 END) as stuck,
                    SUM(CASE WHEN status = 'Completed' THEN amount ELSE 0 END) as revenue
                FROM tasks
                GROUP BY client
                ORDER BY revenue DESC";
        $stmt = $pdo->query($sql);
        $revenueByClient = $stmt->fetchAll();

        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Initialize FPDF
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        
        // Styling Colors
        $primaryColor = [37, 99, 235]; // #2563eb Blue

        // Document Title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 10, 'Office Management Report', 0, 1, 'L');
        
        // Date generated
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 5, 'Generated: ' . date('d/m/Y h:i A'), 0, 1, 'L');
        $pdf->Ln(8);

        // Stats Cards Divider
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Summary Analytics', 0, 1, 'L');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 7, 'Total Revenue: ', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 7, 'Rs ' . number_format($revenue, 2), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 7, 'Completed Tasks: ', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 7, $completedTasks, 0, 1, 'L');

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 7, 'Pending Tasks: ', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 7, $pendingTasks, 0, 1, 'L');

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 7, 'Stuck Tasks: ', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 7, $stuckTasks, 0, 1, 'L');
        $pdf->Ln(10);

        // Employee performance header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Tax Expert Detailed Performance', 0, 1, 'L');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(4);

        // Performance Table Headers
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 8, 'Expert Name', 1, 0, 'L');
        $pdf->Cell(28, 8, 'Assigned', 1, 0, 'C');
        $pdf->Cell(28, 8, 'Completed', 1, 0, 'C');
        $pdf->Cell(28, 8, 'Pending', 1, 0, 'C');
        $pdf->Cell(26, 8, 'Stuck', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Revenue', 1, 1, 'R');

        // Populate Table Rows
        $pdf->SetFont('Arial', '', 9);
        foreach ($employeePerformance as $emp) {
            $pdf->Cell(40, 8, $emp['name'], 1, 0, 'L');
            $pdf->Cell(28, 8, $emp['total'], 1, 0, 'C');
            $pdf->Cell(28, 8, $emp['completed'], 1, 0, 'C');
            $pdf->Cell(28, 8, $emp['pending'], 1, 0, 'C');
            $pdf->Cell(26, 8, $emp['stuck'], 1, 0, 'C');
            $pdf->Cell(30, 8, 'Rs ' . number_format($emp['revenue'], 2), 1, 1, 'R');
        }
        $pdf->Ln(10);

        // Revenue by Source / Client header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Revenue by Source (Client)', 0, 1, 'L');
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 8, 'Source / Client', 1, 0, 'L');
        $pdf->Cell(28, 8, 'Total Tasks', 1, 0, 'C');
        $pdf->Cell(28, 8, 'Completed', 1, 0, 'C');
        $pdf->Cell(28, 8, 'Pending', 1, 0, 'C');
        $pdf->Cell(26, 8, 'Stuck', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Revenue', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        foreach ($revenueByClient as $src) {
            $pdf->Cell(40, 8, $src['client'], 1, 0, 'L');
            $pdf->Cell(28, 8, $src['total'], 1, 0, 'C');
            $pdf->Cell(28, 8, $src['completed'], 1, 0, 'C');
            $pdf->Cell(28, 8, $src['pending'], 1, 0, 'C');
            $pdf->Cell(26, 8, $src['stuck'], 1, 0, 'C');
            $pdf->Cell(30, 8, 'Rs ' . number_format($src['revenue'], 2), 1, 1, 'R');
        }

        // Serve PDF to browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="office-report-' . date('Y-m-d') . '.pdf"');
        $pdf->Output('I');
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to process report request', 'error' => $e->getMessage()]);
}
