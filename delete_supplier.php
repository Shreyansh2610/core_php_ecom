<?php
require 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {

        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");


        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: home.php");
        exit;
    } catch (PDOException $e) {
        // Redirect back with error message
        header("Location: home.php?error=Supplier%20cannot%20be%20deleted.%20It%20is%20linked%20to%20items.");
        exit;
    }
}
