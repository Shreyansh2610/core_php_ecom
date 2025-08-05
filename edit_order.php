<?php
// edit_order.php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    die("Invalid order ID.");
}

// Define status list with English values (stored in DB) and Italian labels (for display)
$statusOptions = [
    'pending' => 'in attesa',
    'confirmed' => 'confermato',
    'complete' => 'completato',
    'complete' => 'completato',
    'canceled' => 'annullato',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    if (!array_key_exists($status, $statusOptions)) {
        die("Invalid status.");
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = ?, created_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    header("Location: view_orders.php");
    exit;
}


// Fetch current order info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
$brandQuery = $pdo->prepare("SELECT DISTINCT items.brand FROM order_items 
    LEFT JOIN items ON items.id = order_items.item_id 
    WHERE order_items.order_id = ? 
    LIMIT 1");

$brandQuery->execute([$order_id]);

$orderBrand = $brandQuery->fetchColumn();

$currentItemsQuery = $pdo->prepare("SELECT * FROM order_items 
    WHERE order_items.order_id = ?");

$currentItemsQuery->execute([$order_id]);

$currentItems = $currentItemsQuery->fetchAll();
var_dump($currentItems);
$itemsListQuery = $pdo->prepare("SELECT * FROM items 
    WHERE items.brand = ?");

$itemsListQuery->execute([$orderBrand]);

$itemsLists = $itemsListQuery->fetchAll();

$suppliers = $pdo->query("SELECT id, name, address, phone, email, vat_number, sales_contact FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$brands = $pdo->query("SELECT id, brand FROM brands ORDER BY brand")->fetchAll(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Modifica Stato Ordine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function fetchBrands(supplierId) {
            // fetch(`get_brands_by_supplier.php?supplier_id=${supplierId}`)
            //   .then(res => res.json())
            //   .then(data => {
            //     const brandSelect = document.getElementById('brand');
            //     const itemList = document.getElementById('item_list');
            //     brandSelect.innerHTML = '<option value="">-- Select Brand --</option>';
            //     itemList.innerHTML = '';
            //     data.forEach(brand => {
            //       brandSelect.innerHTML += `<option value="${brand}">${brand}</option>`;
            //     });
            //   });
            // get_supplier_data_by_supplier

            fetch(`get_supplier_data_by_supplier.php?supplier_id=${supplierId}`)
                .then(res => res.json())
                .then(data => {
                    console.log(data)
                    document.querySelector('[name="order_supplier_name"]').value = data['name'] ?? '';
                    document.querySelector('[name="order_supplier_address"]').value = data['address'] ?? '';
                    document.querySelector('[name="order_supplier_email"]').value = data['email'] ?? '';
                    document.querySelector('[name="order_supplier_phone"]').value = data['phone'] ?? '';
                    document.querySelector('[name="order_supplier_vat"]').value = data['vat_number'] ?? '';
                    document.querySelector('[name="order_supplier_sales_contact"]').value = data['sales_contact'] ?? '';
                });
        }

        function fetchItemsByBrand(brandName) {
            fetch(`get_items_by_brand.php?brand=${encodeURIComponent(brandName)}`)
                .then(res => res.json())
                .then(data => {
                    const itemList = document.getElementById('item_list');
                    itemList.innerHTML = '';
                    data.forEach(item => {
                        const imageHtml = item.image ?
                            `<img src="uploads/${encodeURIComponent(item.image)}" alt="Item Image" style="width: 60px; height: 60px;">` :
                            `<span class="text-muted">Nessuna immagine</span>`;

                        itemList.innerHTML += `
      <tr>
        <td>${imageHtml}</td>
            <td>${item.sku}</td>
            <td>${item.name}</td>
            <td>${item.units_per_box}</td>
            <td>
              <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(${item.id}, -1)">−</button>
                <input type="number" id="qty-${item.id}" name="quantities[${item.id}]" min="0" class="form-control text-center" value="0" />
                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(${item.id}, 1)">+</button>
              </div>
            </td>
          </tr>`;
                    });
                });
        }
    </script>

    <script>
        function adjustQuantity(id, change) {
            const input = document.getElementById(`qty-${id}`);
            let value = parseInt(input.value || 0);
            value = Math.max(0, value + change);
            input.value = value;
        }
    </script>
    <style>
        .input-group {
            max-width: 160px;
        }

        .input-group .btn {
            width: 40px;
        }
    </style>
</head>

<body class="container mt-4">
    <h2>Modifica Stato Ordine #<?= htmlspecialchars($order['order_number']) ?></h2>
    <form method="POST">
        <div class="mb-3">
            <label for="status" class="form-label">Stato</label>
            <select name="status" id="status" class="form-select">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($order['status'] ?: 'pending') === $value ? 'selected' : '' ?>>
                        <?= ucfirst($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">

            <label for="supplier" class="form-label">Seleziona fornitore</label>
            <select name="supplier_id" id="supplier" class="form-select" required onchange="fetchBrands(this.value)">
                <option value="">-- Seleziona fornitore --</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>" <?= $supplier['id'] === $order['supplier_id'] ? 'selected' : '' ?>><?= htmlspecialchars($supplier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nome del fornitore</label>
            <input type="text" name="order_supplier_name" class="form-control" value="<?= $order['supplier_name'] ?? '' ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Indirizzo del fornitore</label>
            <textarea name="order_supplier_address" class="form-control" required><?= $order['supplier_address'] ?? '' ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Email del fornitore</label>
            <input type="email" name="order_supplier_email" class="form-control" value="<?= $order['supplier_email'] ?? '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Telefono del fornitore</label>
            <input type="text" name="order_supplier_phone" class="form-control" value="<?= $order['supplier_phone'] ?? '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Partita IVA del fornitore</label>
            <input type="text" name="order_supplier_vat" class="form-control" value="<?= $order['supplier_vat_number'] ?? '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contatto vendite fornitore</label>
            <input type="text" name="order_supplier_sales_contact" class="form-control" value="<?= $order['supplier_vat_number'] ?? '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="brand" class="form-label">Seleziona Marca</label>
            <select name="brand" id="brand" class="form-select" required onchange="fetchItemsByBrand(this.value)">
                <option value="">-- Seleziona Marca --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>" <?= $brand['id'] === $orderBrand ? 'selected' : '' ?>><?= htmlspecialchars($brand['brand']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Data dell'ordine</label>
            <input type="date" name="order_date" class="form-control" value="<?= $order['order_date'] ?? '' ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">numero dell'ordine</label>
            <input type="text" name="order_number" class="form-control" value="<?= $order['order_number'] ?? '' ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Note</label>
            <textarea name="notes" class="form-control"><?= $order['notes'] ?? '' ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Invia ordine tramite</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="send_method" id="sendEmail" value="email" <?= $order['send_method'] == 'email' ? 'checked' : '' ?>>
                <label class="form-check-label" for="sendEmail">E-mail</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="send_method" id="sendWhatsApp" value="whatsapp" <?= $order['send_method'] == 'whatsapp' ? 'checked' : '' ?>>
                <label class="form-check-label" for="sendWhatsApp">WhatsApp</label>
            </div>
        </div>


        <h5>Elementi</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Immagine</th>
                    <th>SKU</th>
                    <th>Nome del prodotto</th>
                    <th>Unità/Scatola</th>
                    <th>Scatola richiesta</th>
                </tr>
            </thead>
            <tbody id="item_list">
                <?php foreach ($itemsLists as $items): ?>
                    <tr>
                        <td>
                            <?php if(isset($items['image'])): ?>
                            <img src="uploads/<?php echo $items['image']; ?>" alt="Item Image" style="width: 60px; height: 60px;">
                            <?php else: ?>
                                <span class="text-muted">Nessuna immagine</span>
                            <?php endif ?>
                        </td>
                        <td><?php echo $items['sku']; ?></td>
                        <td><?php echo $items['name']; ?></td>
                        <td><?php echo $items['units_per_box']; ?></td>
                        <td>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(<?php echo $items['id']; ?>, -1)">−</button>
                                <input type="number" id="qty-${item.id}" name="quantities[<?php echo $items['id']; ?>]" min="0" class="form-control text-center" value="0" />
                                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(<?php echo $items['id']; ?>, 1)">+</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
        <button type="submit" class="btn btn-primary">Aggiorna Stato</button>
        <a href="view_orders.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>

</html>