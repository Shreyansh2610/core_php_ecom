<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header('Location: home.php');
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE suppliers SET name=?, address=?, phone=?, email=?, vat_number=?, sales_contact=? WHERE id=?");
    $stmt->execute([
        $_POST['name'], $_POST['address'], $_POST['phone'],
        $_POST['email'], $_POST['vat_number'], $_POST['sales_contact'], $id
    ]);
    header('Location: home.php#suppliers');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id=?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    echo "Supplier not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modifica fornitore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Modifica fornitore</h3>
    <form method="POST">
        <div class="mb-2"><input name="name" placeholder="Nome Fornitore" class="form-control" value="<?= htmlspecialchars($supplier['name']) ?>" required></div>
        <div class="mb-2"><input name="address" placeholder="indirizzo" class="form-control" value="<?= htmlspecialchars($supplier['address']) ?>"></div>
        <div class="mb-2"><input name="phone" placeholder="telefono" class="form-control" value="<?= htmlspecialchars($supplier['phone']) ?>"></div>
        <div class="mb-2"><input name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($supplier['email']) ?>"></div>
        <div class="mb-2"><input name="vat_number" placeholder="P.IVA" class="form-control" value="<?= htmlspecialchars($supplier['vat_number']) ?>"></div>
        <div class="mb-2"><input name="sales_contact" placeholder="Rappresentante vendite" class="form-control" value="<?= htmlspecialchars($supplier['sales_contact']) ?>"></div>
        <button type="submit" placeholder="Rappresentante vendite" class="btn btn-success">Aggiorna fornitore</button>
        <a href="home.php#suppliers" class="btn btn-secondary">Cancellare</a>
    </form>
</div>
</body>
</html>
