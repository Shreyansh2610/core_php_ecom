<?php
require 'config.php';
require 'vendor/autoload.php'; // Ensure Composer's autoload is included
use Dompdf\Dompdf;
use Dompdf\Options;
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
//use Twilio\Rest\Client;


session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== ''
    ? (int)$_POST['supplier_id']
    : null;

    $order_date = $_POST['order_date'] ?? date('Y-m-d');
    $order_number = $_POST['order_number'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $quantities = $_POST['quantities'] ?? [];
    $send_method = $_POST['send_method'] ?? 'email';
    $brand = $_POST['brand'] ?? '';


    if (!$order_number || empty($quantities)) {
        die("Invalid input.");
    }

    try {
        // Fetch supplier details
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$supplier_id]);
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

        // if (!$supplier) {
        //     throw new Exception("Supplier not found.");
        // }

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

        // ============ DOMPDF START ============
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // for external images
        $dompdf = new Dompdf($options);

        // Build your HTML
        $html = "<h1>Ordine n. {$order_number} del " . date('d/m/Y', strtotime($order_date)) . "</h1><hr>";

        $salesResponsible = $supplier['supplier_responsible'] ?? '';
        $salesCell        = $supplier['supplier_cell'] ?? '';
        $salesEmailPec    = $supplier['supplier_email_pec'] ?? '';
        $sdi              = $supplier['sdi'] ?? '';
        $iban             = $supplier['iban'] ?? '';
        $agent_telephone  = $supplier['agent_telphone'] ?? '';
        $pmnt             = $supplier['payment'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM contact_details WHERE id = ?");
        $stmt->execute([1]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3) Brand + Notes
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
            $stmt->execute([$brand]);
            $brandData = $stmt->fetch(PDO::FETCH_ASSOC);
            // $html .= "<p><strong>Brand:</strong> " . htmlspecialchars($brandData['brand']) . "</p>";

        $html .= <<<HTML
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            td, th {
                border: 1px solid black !important;
                padding: 5px;
                text-align: left;
                vertical-align: top;
            }   
        </style>
        <table  width="100%">
            <tr>
                <td width="100%" colspan="100%" style="padding:3px;border: 1px solid black;">
                    <strong>{$contact['name']}</strong><br>
                    Tel: {$contact['telphone']}<br>
                    Cell: {$contact['cell']}<br>
                    Email: {$contact['email']}<br>
                    Indirizzo: {$contact['address']}<br>
                    P.IVA: {$contact['vat']}<br>
                    <!-- Chiusura: {$contact['closure']}<br> -->
                    Orario scarico merce: {$contact['upload_time']}<br>
                    PEC: {$contact['email_pec']}<br>
                    Resp.: {$contact['responsible']}<br>
                    Codice SDI: {$contact['sdi']}
                </td>
                <!-- <td width="50%" style="padding:3px">
                    Consegna: PRONTA<br>
                    Imballo:<br>
                    Resa:<br>
                    Spedizione:<br>
                    Pagamento: BONIFICO ANTICIPATO<br>
                    Banca:
                </td> -->
            </tr>
        </table>
        <table  width="100%">
            <tr>
                <td width="50%" style="padding:3px;border: 1px solid black;">
                    <strong>{$brandData['brand']}</strong><br>
                    Tel: {$brandData['telephone']}<br>
                    Cell: {$brandData['mobile']}<br>
                    Email: {$brandData['email']}<br>
                    Indririzzo: {$brandData['address_1']}<br>
                    Indririzzo: {$brandData['address_2']}<br>
                    IBAN: {$brandData['iban']}<br>
                    Codice SDI: {$brandData['sdi']}<br>
                    P.IVA: {$brandData['vat']}
                </td>
                <td width="50%" style="padding:3px;border: 1px solid black;">
                    <strong>{$orderSupplierName}</strong><br>
                    Responsabile: {$salesResponsible}<br>
                    Tel: {$orderSupplierPhone}<br>
                    Cell: {$salesCell}<br>
                    Email: {$orderSupplierEmail}<br>
                    Email pec: {$salesEmailPec}<br>
                    Indririzzo: {$orderSupplierAddress}<br>
                    IBAN: {$iban}<br>
                    Codice SDI: {$sdi}<br>
                    P.IVA: {$orderSupplierVat}<br>
                    Pagamento: {$pmnt}
                </td>
            </tr>
        </table>
    HTML;

        

        $html .= "<p><strong>Notes:</strong> {$notes}</p>";
        $html .= "<p><strong>Colli Totali:</strong> " . count($orderItems) . "</p>";

        $html .= "<h3>Prodotti</h3>
              <table border='1' cellpadding='4' cellspacing='0' width='100%'>
                <thead style='background:#D3D3D3'>
                  <tr>
                    <th width='20%'>ARTICOLO / SKU</th>
                    <th width='60%' colspan='2'>DESCIZIONE</th>
                    <th width='10%'>PZ COLLI</th>
                    <th width='10%'>COLLI</th>
                  </tr>
                </thead>
                <tbody>";

        foreach ($orderItems as $item) {
            $imagePath = $item['image'] ? 'uploads/' . $item['image'] : '';
            $imageHtml = ($imagePath && file_exists($imagePath))
                ? '<img src="' . $imagePath . '" height="40">'
                : 'N/A';

            $html .= "<tr>
                    <td>{$item['sku']}</td>
                    <td colspan='2'>{$item['name']}</td>
                    <td>{$item['units_per_box']}</td>
                    <td style='text-align: center;'>{$item['box_requested']}</td>
                  </tr>";
        }

        $html .= "</tbody></table>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save PDF to orders_pdf/ folder with unique filename
        $timestamp = time();
        $sanitized_order_number = preg_replace('/[^A-Za-z0-9_\-]/', '', $order_number);
        $pdfFileName = "order_{$sanitized_order_number}_{$timestamp}.pdf";

        $saveDir = __DIR__ . '/orders_pdf';
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0755, true);
        }
        $pdfFilePath = $saveDir . '/' . $pdfFileName;
        file_put_contents($pdfFilePath, $dompdf->output());

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
        /* $pdo->rollBack();*/
        echo "Error: " . $e->getMessage();
    }
} else {
    header('Location: order.php');
    exit;
}
