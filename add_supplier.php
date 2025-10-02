<?php
// add_supplier.php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $vat_number = $_POST['vat_number'] ?? '';
    $sales_contact = $_POST['sales_contact'] ?? '';
    $agent_telephone = $_POST['agent_telphone'] ?? '';
    $sdi = $_POST['sdi'] ?? '';
    $iban = $_POST['iban'] ?? '';
    $supp_email = $_POST['supplier_email'] ?? '';
    $supp_email_pec = $_POST['supplier_email_pec'] ?? '';
    $supp_cell = $_POST['supplier_cell'] ?? '';
    $supp_res = $_POST['supplier_responsible'] ?? '';
    $payment = $_POST['payment'] ?? '';

    $brand = $_POST['brand'] ?? '';
    $agency_address = $_POST['agency_address'] ?? '';
    $agency_telephone = $_POST['agency_telephone'] ?? '';
    $agency_mobile = $_POST['agency_mobile'] ?? '';
    $agency_address_1 = $_POST['agency_address_1'] ?? '';

    $agency_address_2 = $_POST['agency_address_2'] ?? '';
    $agency_agent = $_POST['agency_agent'] ?? '';
    $agency_email = $_POST['agency_email'] ?? '';
    $agency_pec = $_POST['agency_pec'] ?? '';
    $agency_vat = $_POST['agency_vat'] ?? '';

    $agency_iban = $_POST['agency_iban'] ?? '';
    $agency_sdi = $_POST['agency_sdi'] ?? '';
    $agency_payment = $_POST['agency_payment'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO suppliers (name, address, phone, email, vat_number, sales_contact,agent_telphone,sdi,iban,supplier_email,supplier_email_pec,supplier_cell,supplier_responsible,payment)
                            VALUES (?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $name, 
        $address, 
        $phone, 
        $email, 
        $vat_number, 
        $sales_contact,
        $agent_telephone,
        $sdi,
        $iban,
        $supp_email,
        $supp_email_pec,
        $supp_cell,
        $supp_res,
        $payment,
        $brand,
        $agency_address,
        $agency_telephone,
        $agency_mobile,
        $agency_address_1,
        $agency_address_2,
        $agency_agent,
        $agency_email,
        $agency_pec,
        $agency_vat,
        $agency_iban,
        $agency_sdi,
        $agency_payment,
    ]);

    header('Location: commercial.php');
    exit;
}
?>
