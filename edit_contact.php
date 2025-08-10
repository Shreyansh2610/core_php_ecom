<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header('Location: brand_list.php?error=Item ID missing');
    exit;
}

$id = $_GET['id'];

// Load item details before processing POST
$stmt = $pdo->prepare("SELECT * FROM contact_details WHERE id=?");
$stmt->execute([$id]);
$contact = $stmt->fetch();

if (!$contact) {
    header("Location: brand_list.php?error=Item not found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $telphone = $_POST['telphone'];
    $cell = $_POST['cell'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $vat = $_POST['vat'];
    $closure = $_POST['closure'];
    $email_pec = $_POST['email_pec'];
    $responsible = $_POST['responsible'];
    $sdi = $_POST['sdi'];

    // Start with base query and parameters
    
    $sql = "UPDATE contact_details SET name=?,telphone=?,cell=?,email=?,address=?,vat=?,closure=?,email_pec=?,responsible=?,sdi=?";
    $params = [$brand_save,$telphone,$cell,$email,$address,$vat,$closure,$email_pec,$responsible,$sdi];


    $sql .= " WHERE id=?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: home.php");
    exit;
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Contatto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>Contatto</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" class="form-control mb-2" value="<?= htmlspecialchars($contact['name']) ?>" placeholder="Nome" required>
        <input type="text" name="telphone" class="form-control mb-2" value="<?= htmlspecialchars($contact['telphone']) ?>" placeholder="Telefono" required>
        <input type="text" name="cell" class="form-control mb-2" value="<?= htmlspecialchars($contact['cell']) ?>" placeholder="Cella" required>
        <input type="text" name="email" class="form-control mb-2" value="<?= htmlspecialchars($contact['email']) ?>" placeholder="Email" required>
        <input type="text" name="address" class="form-control mb-2" value="<?= htmlspecialchars($contact['address']) ?>" placeholder="Indirizzo" required>
        <input type="text" name="vat" class="form-control mb-2" value="<?= htmlspecialchars($contact['vat']) ?>" placeholder="IVA" required>
        <input type="text" name="closure" class="form-control mb-2" value="<?= htmlspecialchars($contact['closure']) ?>" placeholder="chiusura" required>
        <input type="text" name="email_pec" class="form-control mb-2" value="<?= htmlspecialchars($contact['email_pec']) ?>" placeholder="Email PEC" required>
        <input type="text" name="responsible" class="form-control mb-2" value="<?= htmlspecialchars($contact['responsible']) ?>" placeholder="Responsabile" required>
        <input type="text" name="sdi" class="form-control mb-2" value="<?= htmlspecialchars($contact['sdi']) ?>" placeholder="SDI" required>

        <button type="submit" class="btn btn-primary">Aggiornamento</button>
        <a href="home.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
