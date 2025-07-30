<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header('Location: home.php?error=Category ID missing');
    exit;
}

$id = $_GET['id'];

// Check if any items are assigned to this category
$stmt = $pdo->prepare("SELECT COUNT(*) FROM item_categories WHERE category_id = ?");
$stmt->execute([$id]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    header('Location: home.php?tab=categories&error=Cannot delete category assigned to one or more items.');
    exit;
}

// Delete the category
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

header('Location: home.php?tab=categories');
exit;
