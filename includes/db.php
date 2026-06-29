<?php
// includes/db.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors from end-users, log them instead

// Set default timezone to local system timezone (IST) to sync with local database time
date_default_timezone_set('Asia/Kolkata');

if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        // Use a session folder inside the project instead of the server's
        // default/shared session path. On many shared hosting accounts the
        // default path (e.g. /tmp) is not writable by your account, which
        // causes session data to silently fail to save -- the user appears
        // to log in successfully (login.php redirects), but the very next
        // request can't find the session and bounces back to login.php.
        $sessionPath = __DIR__ . '/../.sessions';
        if (!is_dir($sessionPath)) {
            @mkdir($sessionPath, 0700, true);
        }
        if (is_dir($sessionPath) && is_writable($sessionPath)) {
            session_save_path($sessionPath);
        } else {
            // Couldn't create/use our own folder - log it so it shows up
            // instead of failing silently.
            error_log('Pinnacle: session folder ' . $sessionPath . ' is not writable, falling back to default session.save_path');
        }

        // Make sure the cookie is valid for the whole site regardless of
        // which page/subfolder set it.
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }
    
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'login.php' && !isset($_SESSION['user_id'])) {
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }
}

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
    // Create users table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Check if users are empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)");
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insert->execute(['admin', $hashedPassword, 'Administrator']);
    }

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
