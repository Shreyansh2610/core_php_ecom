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
        $stmt = $pdo->prepare("INSERT INTO brand (name, sku, brand, units_per_box, supplier_id, image)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $sku, $brand, $units_per_box, $supplier_id, $image_filename]);
        
       $item_id = $pdo->lastInsertId();

        if (!empty($category_ids)) {
            $cat_stmt = $pdo->prepare("INSERT INTO item_categories (item_id, category_id) VALUES (?, ?)");
            foreach ($category_ids as $cat_id) {
                $cat_stmt->execute([$item_id, $cat_id]);
            }
        }

        $pdo->commit();
        header('Location: product_list.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
