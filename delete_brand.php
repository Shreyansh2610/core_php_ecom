<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: brand_list.php?error=Item ID is missing");
    exit;
}

$id = $_GET['id'];

try {
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->execute([$id]);

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    header("Location: brand_list.php");
} catch (PDOException $e) {
    header("Location: brand_list.php?error=Error deleting item: " . urlencode($e->getMessage()));
}
exit;

exit;
