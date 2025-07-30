<?php
// add_supplier.php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $vat_number = $_POST['vat_number'] ?? '';
    $sales_contact = $_POST['sales_contact'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO suppliers (name, address, phone, email, vat_number, sales_contact)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $address, $phone, $email, $vat_number, $sales_contact]);

    header('Location: home.php#suppliers');
    exit;
}
?>
