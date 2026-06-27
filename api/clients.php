<?php
// api/clients.php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $stmt = $pdo->query("SELECT name FROM clients ORDER BY name ASC");
    $clients = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($clients);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch clients', 'error' => $e->getMessage()]);
}
