<?php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$supplier_id = $_GET['supplier_id'] ?? 0;
if (!$supplier_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT DISTINCT brand FROM items WHERE supplier_id = ? AND brand IS NOT NULL ORDER BY brand");
$stmt->execute([$supplier_id]);
$brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($brands);
