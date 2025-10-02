<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header('Location: home.php?error=Item ID missing');
    exit;
}

$id = $_GET['id'];

// Load item details before processing POST
$stmt = $pdo->prepare("SELECT * FROM items WHERE id=?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: home.php?error=Item not found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $sku = $_POST['sku'];
    $brand = $_POST['brand'];
    $units_per_box = $_POST['units_per_box'];
    $supplier_id = $_POST['supplier_id'];

    // Start with base query and parameters
    $params = [$name, $sku, $brand, $units_per_box, $supplier_id];
    $sql = "UPDATE items SET name=?, sku=?, brand=?, units_per_box=?, supplier_id=?";

    // Check if a new image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImage = uniqid('img_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $newImage);

        $sql .= ", image=?";
        $params[] = $newImage;
    }

    $sql .= " WHERE id=?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: product_list.php");
    exit;
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Modifica elemento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Modifica elemento</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" class="form-control mb-2" value="<?= htmlspecialchars($item['name']) ?>" placeholder="Name" required>
        <input type="text" name="sku" class="form-control mb-2" value="<?= htmlspecialchars($item['sku']) ?>" placeholder="SKU">
        <select name="brand" class="form-control mb-2" required>
            <option value="">-- Seleziona Azienda --</option>
            <?php
            $stmt = $pdo->query("SELECT id, brand FROM brands ORDER BY brand");
            while ($row = $stmt->fetch()) {
                echo "<option value='{$row['id']}' ".($item['brand']==$row['id']?'selected':'').">{$row['brand']}</option>";
            }
            ?>
        </select>
        <!-- <input type="text" name="brand" class="form-control mb-2" value="<?= htmlspecialchars($item['brand']) ?>" placeholder="Brand"> -->
        <input type="text" name="units_per_box" class="form-control mb-2" value="<?= $item['units_per_box'] ?>" placeholder="Units per Box">

        <select name="supplier_id" class="form-control mb-2" required>
            <option value="">-- Seleziona Commerciale --</option>
            <?php
            $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name");
            while ($s = $suppliers->fetch()) {
                $selected = $s['id'] == $item['supplier_id'] ? 'selected' : '';
                echo "<option value='{$s['id']}' $selected>{$s['name']}</option>";
            }
            ?>
        </select>

        <div class="mb-2">
            <label>Current Image:</label><br>
            <?php if ($item['image']): ?>
                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Item Image" style="max-height:100px;"><br>
            <?php else: ?>
                <em>No image uploaded</em><br>
            <?php endif; ?>
        </div>
        <input type="file" name="image" class="form-control mb-2">


        <button type="submit" class="btn btn-primary">Aggiorna elemento</button>
        <a href="product_list.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
