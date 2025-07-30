<?php
// add_item.php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $units_per_box = $_POST['units_per_box'] ?? 0;
    $supplier_id = $_POST['supplier_id'] ?? 0;
    $category_ids = $_POST['category_ids'] ?? [];
    
    $image_filename = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = uniqid('item_') . '.' . $image_ext;

        if (move_uploaded_file($image_tmp, $uploadDir . $new_image_name)) {
            $image_filename = $new_image_name;
        }
    }
    

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO items (name, sku, brand, units_per_box, supplier_id, image)
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
        header('Location: home.php?tab=items');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
