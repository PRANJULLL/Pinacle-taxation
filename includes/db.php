<?php
// includes/db.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors from end-users, log them instead

// Set default timezone to local system timezone (IST) to sync with local database time
date_default_timezone_set('Asia/Kolkata');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pinnacle_office');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Automatically seed and apply stuck logic
    seedDatabaseIfNeeded($pdo);
    applyStuckLogic($pdo);

} catch (PDOException $e) {
    // Return a JSON error if it's an API request, or display a styled message
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['message' => 'Database connection failed', 'error' => $e->getMessage()]);
        exit;
    } else {
        die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Database Error</title>
            <style>
                body { background: #f8fafc; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); max-width: 500px; text-align: center; border: 1px solid #e2e8f0; }
                h1 { color: #dc2626; font-size: 1.5rem; margin-bottom: 1rem; }
                p { color: #475569; line-height: 1.5; font-size: 0.95rem; }
                code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; color: #0f172a; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h1>Database Connection Failed</h1>
                <p>Please make sure you have imported <code>schema.sql</code> and your connection credentials in <code>includes/db.php</code> are correct.</p>
                <p style='margin-top: 1rem; text-align: left;'><small>Error details: <code>" . htmlspecialchars($e->getMessage()) . "</code></small></p>
            </div>
        </body>
        </html>");
    }
}

/**
 * Seed database with default clients and employees if they don't exist
 */
function seedDatabaseIfNeeded($pdo) {
    // Check if clients are empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
    if ($stmt->fetchColumn() == 0) {
        $clients = ['Pinnacle', 'Vishnu', 'Clear Tax'];
        $insert = $pdo->prepare("INSERT INTO clients (name) VALUES (?)");
        foreach ($clients as $client) {
            $insert->execute([$client]);
        }
    }

    // Check if employees are empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    if ($stmt->fetchColumn() == 0) {
        $employees = ['Jay', 'Mohan', 'Prem', 'Vivek'];
        $insert = $pdo->prepare("INSERT INTO employees (name) VALUES (?)");
        foreach ($employees as $employee) {
            $insert->execute([$employee]);
        }
    }
}

/**
 * Automark pending tasks older than 24 hours as Stuck
 */
function applyStuckLogic($pdo) {
    $stmt = $pdo->query("UPDATE tasks SET status = 'Stuck' WHERE status = 'Pending' AND createdAt < NOW() - INTERVAL 1 DAY");
}
