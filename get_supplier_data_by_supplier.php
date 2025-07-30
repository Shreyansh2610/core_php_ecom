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

$stmt = $pdo->prepare("SELECT name, address, phone, email, vat_number, sales_contact FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($supplier);
