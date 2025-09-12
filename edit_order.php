<?php
require 'vendor/autoload.php'; // Ensure Composer's autoload is included
// require_once __DIR__ . '/tcpdf/tcpdf_include.php';

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
//use Twilio\Rest\Client;
// use TCPDF;

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
    'canceled' => 'annullato',
    'suspended' => 'sospesa',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $supplier_id = $_POST['supplier_id'] ?? 0;
    $order_date = $_POST['order_date'] ?? date('Y-m-d');
    $order_number = $_POST['order_number'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $quantities = $_POST['quantities'] ?? [];
    $send_method = $_POST['send_method'] ?? 'email';
    $brand = $_POST['brand'] ?? '';
    if (!array_key_exists($status, $statusOptions)) {
        die("Invalid status.");
    }

    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$supplier) {
        throw new Exception("Supplier not found.");
    }

    $orderSupplierName = $_POST['order_supplier_name'] ?? $supplier['name'];
    $orderSupplierAddress = $_POST['order_supplier_address'] ?? $supplier['address'];
    $orderSupplierEmail = $_POST['order_supplier_email'] ?? $supplier['supplier_email'];
    $orderSupplierPhone = $_POST['order_supplier_phone'] ?? $supplier['phone'];
    $orderSupplierVat = $_POST['order_supplier_vat'] ?? $supplier['vat_number'];
    $orderSupplierSalesContact = $_POST['order_supplier_sales_contact'] ?? $supplier['sales_contact'];

    // Fetch all valid item IDs
    $validItemIds = $pdo->query("SELECT id FROM items")->fetchAll(PDO::FETCH_COLUMN);
    $validItemIds = array_map('intval', $validItemIds);
    // Fetch supplier details
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE orders SET status=?, supplier_id =?, order_date=?,order_number=?,notes=?,sent_method=?,supplier_name=?,supplier_address=?,supplier_phone=?,supplier_email=?,supplier_vat_number=?,supplier_sales_contact=?, created_at=NOW() WHERE id = ?");
    $stmt->execute([$status, $supplier_id, $order_date, $order_number, $notes, $send_method, $orderSupplierName, $orderSupplierAddress, $orderSupplierPhone, $orderSupplierEmail, $orderSupplierVat, $orderSupplierSalesContact, $order_id]);

    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);

    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, item_id, box_requested)
        VALUES (?, ?, ?)");

    $orderItems = [];
    foreach ($quantities as $item_id => $box_requested) {
        $item_id = (int)$item_id;
        $box_requested = (int)$box_requested;

        if ($box_requested > 0 && in_array($item_id, $validItemIds)) {
            $itemStmt->execute([$order_id, $item_id, $box_requested]);

            // fetch item details for PDF (inside your foreach)
            $itemDetailsStmt = $pdo->prepare("
                    SELECT sku,
                           name,
                           units_per_box,
                           brand,
                           image
                      FROM items
                     WHERE id = ?
                ");
            $itemDetailsStmt->execute([$item_id]);
            $itemDetails = $itemDetailsStmt->fetch(PDO::FETCH_ASSOC);

            $orderItems[] = [
                'sku'            => $itemDetails['sku'],
                'name'           => $itemDetails['name'],
                'units_per_box'  => $itemDetails['units_per_box'],
                'box_requested'  => $box_requested,
                'brand'          => $itemDetails['brand'],
                'image'          => $itemDetails['image'], // add image filename
            ];
        }
    }

    // Generate PDF
    $pdf = new TCPDF();
    $pdf->AddPage();

    // --- build your HTML in order --- //
    $html  = "";

    // 1) Big header: order number and date
    $html .= "<h1>Ordine n. {$order_number} del " . date('d/m/Y', strtotime($order_date)) . "</h1>";
    $salesResponsible = $supplier['supplier_responsible'] ?? '';
    $salesCell = $supplier['supplier_cell'] ?? '';
    $salesEmailPec = $supplier['supplier_email_pec'] ?? '';
    $sdi = $supplier['sdi'] ?? '';
    $iban = $supplier['iban'] ?? '';
    $agent_telephone = $supplier['agent_telphone'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM contact_details WHERE id = ?");
    $stmt->execute([1]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    // 2) Supplier & Client Info Grid with dynamic supplier info
    $html .= <<<HTML
        <table cellpadding="4">
          <tr>
            <td width="50%" style="border: 1px solid black">
                <table border="0">
                    <tr><td><strong>{$orderSupplierName}</strong></td></tr>
                    <tr><td><strong>Responsabile:</strong> {$salesResponsible}</td></tr>
                    <tr><td><strong>Tel:</strong> {$orderSupplierPhone}</td></tr>
                    <tr><td><strong>Cell:</strong> {$salesCell}</td></tr>
                    <tr><td><strong>Email:</strong> {$orderSupplierEmail}</td></tr>
                    <tr><td><strong>Email pec:</strong> {$salesEmailPec}</td></tr>
                    <tr><td><strong>{$orderSupplierAddress}</strong></td></tr>
                    <tr><td><strong>IBAN:</strong> {$iban}</td></tr>
                    <tr><td><strong>Codice SDI:</strong> {$sdi}</td></tr>
                    <tr><td><strong>P.IVA:</strong> {$orderSupplierVat}</td></tr>
                </table>
            </td>
            <td width="50%" style="border: 1px solid black">
                <table border="0">
                    <tr><td><strong>{$orderSupplierSalesContact}</strong></td></tr>
                    <tr><td><strong>Tel:</strong> {$agent_telephone}</td></tr>
                    <tr><td><strong>Email:</strong> {$supplier['email']}</td></tr>
                </table>
            </td>
          </tr>
          <tr>
           <td width="50%" style="border: 1px solid black">
                <table border="0">
                    <tr><td><strong>Consegna:</strong> PRONTA</td></tr>
                    <tr><td><strong>Imballo:</strong></td></tr>
                    <tr><td><strong>Resa:</strong></td></tr>
                    <tr><td><strong>Spedizione:</strong></td></tr>
                    <tr><td><strong>Pagamento:</strong> BONIFICO ANTICIPATO</td></tr>
                    <tr><td><strong>Banca:</strong></td></tr>
                </table>
            </td>
            <td width="50%" style="border: 1px solid black">
                <table border="0">
                    <tr><td><strong>{$contact['name']}</strong></td></tr>
                    <tr><td><strong>Tel:</strong> {$contact['telphone']}</td></tr>
                    <tr><td><strong>Cell:</strong> {$contact['cell']}</td></tr>
                    <tr><td><strong>Email:</strong> {$contact['email']}</td></tr>
                    <tr><td><strong>Indirizzo:</strong> {$contact['address']}</td></tr>
                    <tr><td><strong>P.IVA:</strong> {$contact['vat']}</td></tr>
                    <tr><td><strong>Chiusura:</strong> {$contact['closure']}</td></tr>
                    <tr><td><strong>PEC:</strong> {$contact['email_pec']}</td></tr>
                    <tr><td><strong>Resp.:</strong> {$contact['responsible']}</td></tr>
                    <tr><td><strong>Codice SDI:</strong> {$contact['sdi']}</td></tr>
                </table>
            </td>
          </tr>
        </table>
        HTML;

    // 3) Brand + Notes
    if (!empty($brand)) {
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$brand]);
        $brandData = $stmt->fetch(PDO::FETCH_ASSOC);
        $html .= "<p><strong>Brand:</strong> " . htmlspecialchars($brandData['brand']) . "</p>";
    }
    $html .= "<p><strong>Notes:</strong> {$notes}</p>";
    $totalOrders = count($orderItems);
    $html .= "<p><strong>Ordini totali:</strong> {$totalOrders}</p>";

    // 4) Items table

    $html .= "<h3>Prodotti</h3>";
    $html .= "<table border='1' cellpadding='4' cellspacing='0' width='100%'>
            <thead style='background:#D3D3D3'>
              <tr>
                <th width='15%'>SKU</th>
                <th width='30%'>Nome del prodotto</th>
                <th width='5%'>Unità/Scatola</th>
                <th width='5%'>Scatole</th>
                <th width='25%'>Marca</th>
                <th width='20%'>Immagine</th>
              </tr>
             </thead>
          </table>";
    $pdf->writeHTML($html, true, false, true, false, '');

    // Get current position (X/Y) after header
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Draw a horizontal line
    $pdf->Line($x, $y, $x + 190, $y); // 190 = width of line in mm
    $pdf->Ln(2); // Move down a bit for spacing





    $html = "<table border='1' cellpadding='4' cellspacing='0' width='100%'><tbody>";

    foreach ($orderItems as $item) {
        $imagePath = $item['image'] ? 'uploads/' . $item['image'] : ''; // full path

        // Resize image HTML if available
        $imageHtml = $imagePath && file_exists($imagePath)
            ? '<img src="' . $imagePath . '" height="40">'
            : 'N/A';

        $html .= "<tr>
                <td width='15%'>{$item['sku']}</td>
                <td width='30%'>{$item['name']}</td>
                <td width='5%'>{$item['units_per_box']}</td>
                <td width='5%'>{$item['box_requested']}</td>
                <td width='25%'>{$item['brand']}</td>
                <td width='20%'>{$imageHtml}</td>
              </tr>";
    }

    $html .= "</tbody></table>";
    $pdf->writeHTML($html, true, false, true, false, '');




    // Save PDF to orders_pdf/ folder with unique filename
    $timestamp = time();
    $sanitized_order_number = preg_replace('/[^A-Za-z0-9_\-]/', '', $order_number);
    $pdfFileName = "order_{$sanitized_order_number}_{$timestamp}.pdf";

    $saveDir = __DIR__ . '/orders_pdf';
    if (!file_exists($saveDir)) {
        mkdir($saveDir, 0755, true);
    }
    $pdfFilePath = $saveDir . '/' . $pdfFileName;
    $pdf->Output($pdfFilePath, 'F');

    // Save pdf_filename to orders table
    $updateStmt = $pdo->prepare("UPDATE orders SET pdf_filename = ? WHERE id = ?");
    $updateStmt->execute([$pdfFileName, $order_id]);

    // Generate public link (adjust with your actual domain/path)
    $publicPdfUrl = "./orders_pdf/{$pdfFileName}";
    $pdo->commit();

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
    <title>Modifica ordine</title>
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

            // fetch(`get_supplier_data_by_supplier.php?supplier_id=${supplierId}`)
            //     .then(res => res.json())
            //     .then(data => {
            //         document.querySelector('[name="order_supplier_name"]').value = data['name'] ?? '';
            //         document.querySelector('[name="order_supplier_address"]').value = data['address'] ?? '';
            //         document.querySelector('[name="order_supplier_email"]').value = data['email'] ?? '';
            //         document.querySelector('[name="order_supplier_phone"]').value = data['phone'] ?? '';
            //         document.querySelector('[name="order_supplier_vat"]').value = data['vat_number'] ?? '';
            //         document.querySelector('[name="order_supplier_sales_contact"]').value = data['sales_contact'] ?? '';
            //     });
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

                    function adjustQuantity(id, change) {
                        const input = document.getElementById(`qty-${id}`);
                        console.log(input);
                        
                        let value = parseInt(input.value ?? 0);
                        value = Math.max(0, value + change);
                        input.value = value;
                    }
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

            <label for="supplier" class="form-label">Seleziona Commerciale</label>
            <select name="supplier_id" id="supplier" class="form-select" required onchange="fetchBrands(this.value)">
                <option value="">-- Seleziona Commerciale --</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>" <?= $supplier['id'] === $order['supplier_id'] ? 'selected' : '' ?>><?= htmlspecialchars($supplier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- <div class="mb-3">
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
        </div> -->

        <div class="mb-3">
            <label for="brand" class="form-label">Seleziona Azienda</label>
            <select name="brand" id="brand" class="form-select" required onchange="fetchItemsByBrand(this.value)">
                <option value="">-- Seleziona Azienda --</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>" <?= $brand['id'] == $orderBrand ? 'selected' : '' ?>><?= htmlspecialchars($brand['brand']) ?></option>
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
                <input class="form-check-input" type="radio" name="send_method" id="sendEmail" value="email" <?= $order['sent_method'] == 'email' ? 'checked' : '' ?>>
                <label class="form-check-label" for="sendEmail">E-mail</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="send_method" id="sendWhatsApp" value="whatsapp" <?= $order['sent_method'] == 'whatsapp' ? 'checked' : '' ?>>
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
                    <?php
                    $currentItemsQuery = $pdo->prepare("SELECT box_requested FROM order_items WHERE order_items.order_id = ? AND order_items.item_id = ?");

                    $currentItemsQuery->execute([$order_id, $items['id']]);

                    $currentItems = $currentItemsQuery->fetchColumn();
                    ?>
                    <tr>
                        <td>
                            <?php if (isset($items['image'])): ?>
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
                                <input type="number" id="qty-<?php echo $items['id']; ?>" name="quantities[<?php echo $items['id']; ?>]" min="0" class="form-control text-center" value="<?php echo ($currentItems ? $currentItems : 0); ?>" />
                                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(<?php echo $items['id']; ?>, 1)">+</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
        <button type="submit" class="btn btn-primary">Aggiornamento</button>
        <a href="view_orders.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>

</html>