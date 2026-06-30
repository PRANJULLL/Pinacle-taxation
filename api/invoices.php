<?php
// api/invoices.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/invoice_generator.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    if ($method === 'GET') {
        if ($action === 'download') {
            // -------------------------------------------------------------
            // DOWNLOAD INVOICE PDF FILE
            // -------------------------------------------------------------
            $invoiceNumber = isset($_GET['invoiceNumber']) ? trim($_GET['invoiceNumber']) : '';
            if (empty($invoiceNumber)) {
                http_response_code(400);
                echo json_encode(['message' => 'Invoice number is required']);
                exit;
            }

            // Find invoice
            $stmt = $pdo->prepare("SELECT * FROM invoices WHERE invoiceNumber = ?");
            $stmt->execute([$invoiceNumber]);
            $invoice = $stmt->fetch();
            if (!$invoice) {
                http_response_code(404);
                echo json_encode(['message' => 'Invoice not found']);
                exit;
            }

            $filePath = $invoice['pdfPath'];
            // If the path doesn't exist on disk, regenerate it
            if (empty($filePath) || !file_exists($filePath)) {
                // Find associated task
                $stmt = $pdo->prepare("SELECT * FROM tasks WHERE invoiceId = ?");
                $stmt->execute([$invoice['id']]);
                $task = $stmt->fetch();
                if ($task) {
                    $filePath = generateInvoicePDF([
                        'invoiceNumber' => $invoice['invoiceNumber'],
                        'customerName' => $task['customerName'],
                        'amount' => $task['amount'],
                        'plan' => $task['plan'],
                        'date' => $invoice['createdAt'],
                        'orderId' => $task['orderId'],
                        'pan' => $task['pan'],
                        'phone' => $task['phone'],
                        'email' => $task['email'],
                        'client' => $task['client'],
                    ]);

                    // Update pdfPath in DB
                    $update = $pdo->prepare("UPDATE invoices SET pdfPath = ? WHERE id = ?");
                    $update->execute([$filePath, $invoice['id']]);
                } else {
                    http_response_code(404);
                    echo json_encode(['message' => 'PDF missing and associated task not found for regeneration']);
                    exit;
                }
            }

            // Clear buffer
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Serve download response headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $invoiceNumber . '.pdf"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            // -------------------------------------------------------------
            // LIST ALL INVOICES
            // -------------------------------------------------------------
            $stmt = $pdo->query("SELECT * FROM invoices ORDER BY createdAt DESC");
            $invoices = $stmt->fetchAll();
            foreach ($invoices as &$inv) {
                $inv['amount'] = (float)$inv['amount'];
            }
            echo json_encode($invoices);
            exit;
        }
    } 
    
    elseif ($method === 'POST') {
        if ($action === 'generate') {
            // -------------------------------------------------------------
            // GENERATE NEW INVOICE
            // -------------------------------------------------------------
            $taskId = isset($_GET['taskId']) ? trim($_GET['taskId']) : '';
            if (empty($taskId)) {
                http_response_code(400);
                echo json_encode(['message' => 'Task ID is required for generating invoice']);
                exit;
            }

            // Fetch task
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            if (!$task) {
                http_response_code(404);
                echo json_encode(['message' => 'Task not found']);
                exit;
            }

            // If task already has an invoice, check if the amount or plan has changed.
            // If so, regenerate the PDF and update the invoice record with the new values.
            if (!empty($task['invoiceId'])) {
                $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
                $stmt->execute([$task['invoiceId']]);
                $existingInvoice = $stmt->fetch();

                if ($existingInvoice) {
                    $amountChanged = (float)$existingInvoice['amount'] !== (float)$task['amount'];
                    $planChanged   = $existingInvoice['plan'] !== $task['plan'];
                    $nameChanged   = $existingInvoice['customerName'] !== $task['customerName'];

                    if ($amountChanged || $planChanged || $nameChanged) {
                        // Delete old PDF from disk if it exists
                        if (!empty($existingInvoice['pdfPath']) && file_exists($existingInvoice['pdfPath'])) {
                            @unlink($existingInvoice['pdfPath']);
                        }

                        // Regenerate PDF with current task data
                        $newPdfPath = generateInvoicePDF([
                            'invoiceNumber' => $existingInvoice['invoiceNumber'],
                            'customerName'  => $task['customerName'],
                            'amount'        => $task['amount'],
                            'plan'          => $task['plan'],
                            'date'          => $existingInvoice['createdAt'],
                            'orderId'       => $task['orderId'],
                            'pan'           => $task['pan'],
                            'phone'         => $task['phone'],
                            'email'         => $task['email'],
                            'client'        => $task['client'],
                        ]);

                        // Update invoice record with new amount, plan, customerName, and PDF path
                        $updateInv = $pdo->prepare(
                            "UPDATE invoices SET amount = ?, plan = ?, customerName = ?, pdfPath = ? WHERE id = ?"
                        );
                        $updateInv->execute([
                            $task['amount'],
                            $task['plan'],
                            $task['customerName'],
                            $newPdfPath,
                            $existingInvoice['id'],
                        ]);

                        // Re-fetch updated invoice and return it
                        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
                        $stmt->execute([$existingInvoice['id']]);
                        $updatedInvoice = $stmt->fetch();
                        $updatedInvoice['amount'] = (float)$updatedInvoice['amount'];

                        echo json_encode($updatedInvoice);
                        exit;
                    }

                    // Nothing changed — return the existing invoice as-is
                    $existingInvoice['amount'] = (float)$existingInvoice['amount'];
                    echo json_encode($existingInvoice);
                    exit;
                }
            }

            // Generate invoice number
            $year = date('Y');
            $stmt = $pdo->query("SELECT COUNT(*) FROM invoices");
            $count = $stmt->fetchColumn();
            $invoiceNumber = "INV-" . $year . "-" . str_pad($count + 1, 5, '0', STR_PAD_LEFT);

            // Generate PDF File
            $pdfPath = generateInvoicePDF([
                'invoiceNumber' => $invoiceNumber,
                'customerName' => $task['customerName'],
                'amount' => $task['amount'],
                'plan' => $task['plan'],
                'date' => date('Y-m-d H:i:s'),
                'orderId' => $task['orderId'],
                'pan' => $task['pan'],
                'phone' => $task['phone'],
                'email' => $task['email'],
                'client' => $task['client'],
            ]);

            // Save Invoice details in database
            $sql = "INSERT INTO invoices (invoiceNumber, taskId, customerName, amount, plan, pdfPath, createdAt) 
                    VALUES (:invoiceNumber, :taskId, :customerName, :amount, :plan, :pdfPath, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'invoiceNumber' => $invoiceNumber,
                'taskId' => $task['id'],
                'customerName' => $task['customerName'],
                'amount' => $task['amount'],
                'plan' => $task['plan'],
                'pdfPath' => $pdfPath
            ]);

            $newInvoiceId = $pdo->lastInsertId();

            // Link Invoice back to Task
            $updateTask = $pdo->prepare("UPDATE tasks SET invoiceId = ? WHERE id = ?");
            $updateTask->execute([$newInvoiceId, $task['id']]);

            // Return new invoice details
            $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$newInvoiceId]);
            $newInvoice = $stmt->fetch();
            $newInvoice['amount'] = (float)$newInvoice['amount'];

            http_response_code(201);
            echo json_encode($newInvoice);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to process invoices', 'error' => $e->getMessage()]);
}
