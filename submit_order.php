<?php
require 'config.php';
require 'vendor/autoload.php'; // Ensure Composer's autoload is included

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
//use Twilio\Rest\Client;
use TCPDF;

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'] ?? 0;
    $order_date = $_POST['order_date'] ?? date('Y-m-d');
    $order_number = $_POST['order_number'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $quantities = $_POST['quantities'] ?? [];
    $send_method = $_POST['send_method'] ?? 'email';
    $brand = $_POST['brand'] ?? '';


    if (!$supplier_id || !$order_number || empty($quantities)) {
        die("Invalid input.");
    }

    try {
        // Fetch supplier details
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

        $pdo->beginTransaction();

        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (supplier_id, order_date, order_number, notes, sent_method,supplier_name,supplier_address,supplier_phone,supplier_email,supplier_vat_number,supplier_sales_contact)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $order_date, $order_number, $notes, $send_method, $orderSupplierName, $orderSupplierAddress, $orderSupplierPhone, $orderSupplierEmail, $orderSupplierVat, $orderSupplierSalesContact]);
        $order_id = $pdo->lastInsertId();

        // Insert order items
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
        $salesResponsible = $supplier['supplier_responsible']??'';
        $salesCell = $supplier['supplier_cell']??'';
        $salesEmailPec = $supplier['supplier_email_pec']??'';
        $sdi = $supplier['sdi']??'';
        $iban = $supplier['iban']??'';
        $agent_telephone = $supplier['agent_telphone']??'';

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


        // Send PDF link via selected method
        if ($send_method === 'email') {
            /*$mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'localhost'; // Use your actual SMTP host or leave as 'localhost' if mail() is configured
                $mail->SMTPAuth = false;
                $mail->Port = 25;

                $mail->setFrom('asadsajjad823@gmail.com', 'Warehouse');
                $mail->addAddress($supplier['email'], $supplier['name']);

                $mail->isHTML(true);
                $mail->Subject = "New Order #{$order_number}";
                $mail->Body = "
                    Dear {$supplier['name']},<br><br>
                    Your order has been generated. Please download it from the link below:<br>
                    <a href='{$publicPdfUrl}'>{$publicPdfUrl}</a><br><br>
                    Best regards,<br>
                    Warehouse Team
                ";

                $mail->send();
            } catch (Exception $e) {
                throw new Exception("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }*/
        } elseif ($send_method === 'whatsapp') {
            // Twilio credentials
            /*$sid = 'your_twilio_sid';
            $token = 'your_twilio_auth_token';
            $twilioNumber = 'whatsapp:+14155238886'; // Your Twilio WhatsApp number

            $client = new Client($sid, $token);

            $client->messages->create(
                'whatsapp:' . $supplier['phone'],
                [
                    'from' => $twilioNumber,
                    'body' => "Dear {$supplier['name']}, please view your order #{$order_number} here: {$publicPdfUrl}"
                ]
            );*/
        }

        $pdo->commit();

        header("Location: order.php?success=1");
        exit;
    } catch (Exception $e) {
        /* $pdo->rollBack();
        echo "Error: " . $e->getMessage();*/
    }
} else {
    header('Location: order.php');
    exit;
}
