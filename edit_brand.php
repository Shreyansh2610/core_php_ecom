<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header('Location: brand_list.php?error=Item ID missing');
    exit;
}

$id = $_GET['id'];

// Load item details before processing POST
$stmt = $pdo->prepare("SELECT * FROM brands WHERE id=?");
$stmt->execute([$id]);
$brand = $stmt->fetch();

if (!$brand) {
    header("Location: brand_list.php?error=Item not found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_save = $_POST['brand'];

    // Start with base query and parameters
    $params = [$brand_save];
    $sql = "UPDATE brands SET brand=?";


    $sql .= " WHERE id=?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: brand_list.php");
    exit;
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Modifica marchio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Modifica marchio</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="brand" class="form-control mb-2" value="<?= htmlspecialchars($brand['brand']) ?>" placeholder="Brand" required>

        <button type="submit" class="btn btn-primary">Aggiorna elemento</button>
        <a href="brand_list.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
