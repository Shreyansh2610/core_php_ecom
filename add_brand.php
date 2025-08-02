<?php
// add_item.php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['brand'] ?? '';


    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO brands (brand)
                                VALUES (?)");
        $stmt->execute([$name]);
        $pdo->commit();
        header('Location: brand_list.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
