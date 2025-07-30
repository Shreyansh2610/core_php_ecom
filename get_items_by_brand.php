<?php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$supplier_id = $_GET['supplier_id'] ?? 0;
$brand = $_GET['brand'] ?? '';

if (!$supplier_id || !$brand) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT sku, id, name, units_per_box, image FROM items WHERE supplier_id = ? AND brand = ? ORDER BY name");
$stmt->execute([$supplier_id, $brand]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
