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
    $name = $_POST['brand'] ?? '';
    $adr1 = $_POST['address_1'] ?? '';
    $adr2 = $_POST['address_2'] ?? '';
    $tel = $_POST['telephone'] ?? '';
    $mob = $_POST['mobile'] ?? '';
    $agent = $_POST['agent'] ?? '';
    $email = $_POST['email'] ?? '';
    $pec = $_POST['pec'] ?? '';
    $vat = $_POST['vat'] ?? '';
    $iban = $_POST['iban'] ?? '';
    $sdi = $_POST['sdi'] ?? '';
    $payment = $_POST['payment'] ?? '';

    $sql = "UPDATE brands 
            SET brand=?, address_1=?, address_2=?, telephone=?, mobile=?, agent=?, email=?, pec=?, vat=?, iban=?, sdi=?, payment=? 
            WHERE id=?";

    $params = [
        $name, $adr1, $adr2, $tel, $mob, $agent, $email, $pec, $vat, $iban, $sdi, $payment, $id
    ];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: brand_list.php");
    exit;
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Modifica agenzia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">
    <h3>Modifica agenzia

    </h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="brand" class="form-label">Nome</label>
                    <input type="text" name="brand" id="brand" class="form-control" value="<?= htmlspecialchars($brand['brand']) ?>" placeholder="Azienda">
                </div>
                <div class="mb-3">
                    <label for="telephone" class="form-label">Telefono</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" value="<?= htmlspecialchars($brand['telephone']) ?>" placeholder="Telefono">
                </div>
                <div class="mb-3">
                    <label for="mobile" class="form-label">Cellulare</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" value="<?= htmlspecialchars($brand['mobile']) ?>" placeholder="Cellulare">
                </div>
                <div class="mb-3">
                    <label for="address_1" class="form-label">Indirizzo 1</label>
                    <input type="text" name="address_1" id="address_1" class="form-control" value="<?= htmlspecialchars($brand['address_1']) ?>" placeholder="Indirizzo 1">
                </div>
                <div class="mb-3">
                    <label for="address_2" class="form-label">Indirizzo 2</label>
                    <input type="text" name="address_2" id="address_2" class="form-control" value="<?= htmlspecialchars($brand['address_2']) ?>" placeholder="Indirizzo 2">
                </div>
                <div class="mb-3">
                    <label for="agent" class="form-label">Responsabile Vendite (Agente)</label>
                    <input type="text" name="agent" id="agent" class="form-control" value="<?= htmlspecialchars($brand['agent']) ?>" placeholder="Indirizzo 2">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" name="email" id="email" class="form-control" value="<?= htmlspecialchars($brand['email']) ?>" placeholder="Email">
                </div>
                <div class="mb-3">
                    <label for="pec" class="form-label">PEC</label>
                    <input type="text" name="pec" id="pec" class="form-control" value="<?= htmlspecialchars($brand['pec']) ?>" placeholder="PEC">
                </div>
                <div class="mb-3">
                    <label for="vat" class="form-label">P.Iva</label>
                    <input type="text" name="vat" id="vat" class="form-control" value="<?= htmlspecialchars($brand['vat']) ?>" placeholder="P.Iva">
                </div>
                <div class="mb-3">
                    <label for="iban" class="form-label">IBAN</label>
                    <input type="text" name="iban" id="iban" class="form-control" value="<?= htmlspecialchars($brand['iban']) ?>" placeholder="IBAN">
                </div>
                <div class="mb-3">
                    <label for="iban" class="form-label">SDI</label>
                    <input type="text" name="sdi" id="sdi" class="form-control" value="<?= htmlspecialchars($brand['sdi']) ?>" placeholder="SDI">
                </div>
                <div class="mb-3">
                    <label for="payment" class="form-label">Payment</label>
                    <input type="text" name="payment" id="payment" class="form-control" value="<?= htmlspecialchars($brand['payment']) ?>" placeholder="Payment">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Aggiorna elemento</button>
        <a href="brand_list.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>

</html>