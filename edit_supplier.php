<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header('Location: home.php');
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE suppliers SET name=?, address=?, phone=?, email=?, vat_number=?, sales_contact=?,agent_telphone=?,sdi=?,iban=?,supplier_email=?,supplier_email_pec=?,supplier_cell=?,supplier_responsible=?,payment=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['address'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['vat_number'],
        $_POST['sales_contact'],
        $_POST['agent_telphone'],
        $_POST['sdi'],
        $_POST['iban'],
        $_POST['supplier_email'],
        $_POST['supplier_email_pec'],
        $_POST['supplier_cell'],
        $_POST['supplier_responsible'],
        $_POST['payment'],
        $_POST['brand'],
        $_POST['agency_address'],
        $_POST['agency_telephone'],
        $_POST['agency_mobile'],
        $_POST['agency_address_1'],
        $_POST['agency_address_2'],
        $_POST['agency_agent'],
        $_POST['agency_email'],
        $_POST['agency_pec'],
        $_POST['agency_vat'],
        $_POST['agency_iban'],
        $_POST['agency_sdi'],
        $_POST['agency_payment'],
        $id,
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
    <title>Modifica Commerciale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h3>Modifica Commerciale</h3>
        <form method="POST">
            <div class="mb-2"><input name="name" placeholder="Nome Fornitore" class="form-control" value="<?= htmlspecialchars($supplier['name']) ?>" required></div>
            <div class="mb-2"><input name="address" placeholder="indirizzo" class="form-control" value="<?= htmlspecialchars($supplier['address']) ?>"></div>
            <div class="mb-2"><input name="phone" placeholder="telefono" class="form-control" value="<?= htmlspecialchars($supplier['phone']) ?>"></div>
            <div class="mb-2"><input name="email" class="form-control" placeholder="E-mail dell'agente" value="<?= htmlspecialchars($supplier['email']) ?>"></div>
            <div class="mb-2"><input name="vat_number" placeholder="P.IVA" class="form-control" value="<?= htmlspecialchars($supplier['vat_number']) ?>"></div>
            <div class="mb-2"><input name="sales_contact" placeholder="Rappresentante vendite" class="form-control" value="<?= htmlspecialchars($supplier['sales_contact']) ?>"></div>
            <input type="text" name="agent_telphone" class="form-control mb-2" placeholder="Telefono dell'agente" value="<?= htmlspecialchars($supplier['agent_telphone']) ?>">
            <input type="text" name="sdi" class="form-control mb-2" placeholder="SDI" value="<?= htmlspecialchars($supplier['sdi']) ?>">
            <input type="text" name="iban" class="form-control mb-2" placeholder="IBAN" value="<?= htmlspecialchars($supplier['iban']) ?>">
            <input type="text" name="supplier_email" class="form-control mb-2" placeholder="E-mail del fornitore" value="<?= htmlspecialchars($supplier['supplier_email']) ?>">
            <input type="text" name="supplier_email_pec" class="form-control mb-2" placeholder="Email fornitore pec" value="<?= htmlspecialchars($supplier['supplier_email_pec']) ?>">
            <input type="text" name="supplier_cell" class="form-control mb-2" placeholder="Cellulare del fornitore" value="<?= htmlspecialchars($supplier['supplier_cell']) ?>">
            <input type="text" name="supplier_responsible" class="form-control mb-2" placeholder="Responsabile" value="<?= htmlspecialchars($supplier['supplier_responsible']) ?>">
            <input type="text" name="payment" class="form-control mb-2" placeholder="Pagamento" value="<?= htmlspecialchars($supplier['payment']) ?>">
            <hr>

            <?php
            $brands = $pdo->query("SELECT id, brand FROM brands ORDER BY brand")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <select name="brand" id="brand" class="form-select mb-2" required onchange="fetchItemsByBrand(this.value)">
                <option value="">-- Seleziona azienda --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['brand'] ?>" <?= $supplier['brand']==$brand['brand'] ?>><?= htmlspecialchars($brand['brand']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="agency_address" id="address" class="form-control mb-2" placeholder="Indirizzo" value="<?= htmlspecialchars($supplier['agency_address']) ?>">
            <input type="text" name="agency_telephone" id="telephone" class="form-control mb-2" placeholder="Telefono" value="<?= htmlspecialchars($supplier['agency_telephone']) ?>">
            <input type="text" name="agency_mobile" id="mobile" class="form-control mb-2" placeholder="Cellulare" value="<?= htmlspecialchars($supplier['agency_mobile']) ?>">
            <input type="text" name="agency_address_1" id="address_1" class="form-control mb-2" placeholder="Indirizzo 1" value="<?= htmlspecialchars($supplier['agency_address_1']) ?>">
            <input type="text" name="agency_address_2" id="address_2" class="form-control mb-2" placeholder="Indirizzo 2" value="<?= htmlspecialchars($supplier['agency_address_2']) ?>">
            <input type="text" name="agency_agent" id="agent" class="form-control mb-2" placeholder="Indirizzo 2" value="<?= htmlspecialchars($supplier['agency_agent']) ?>">
            <input type="text" name="agency_email" id="email" class="form-control mb-2" placeholder="Email" value="<?= htmlspecialchars($supplier['agency_email']) ?>">
            <input type="text" name="agency_pec" id="pec" class="form-control mb-2" placeholder="PEC" value="<?= htmlspecialchars($supplier['agency_pec']) ?>">
            <input type="text" name="agency_vat" id="vat" class="form-control mb-2" placeholder="P.Iva" value="<?= htmlspecialchars($supplier['agency_vat']) ?>">
            <input type="text" name="agency_iban" id="iban" class="form-control mb-2" placeholder="IBAN" value="<?= htmlspecialchars($supplier['agency_iban']) ?>">
            <input type="text" name="agency_sdi" id="sdi" class="form-control mb-2" placeholder="SDI" value="<?= htmlspecialchars($supplier['agency_sdi']) ?>">
            <input type="text" name="agency_payment" id="payment" class="form-control mb-2" placeholder="Payment" value="<?= htmlspecialchars($supplier['agency_payment']) ?>">

            <button type="submit" placeholder="Rappresentante vendite" class="btn btn-success my-5">Aggiorna Commerciale</button>
            <a href="home.php#suppliers" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
</body>

</html>