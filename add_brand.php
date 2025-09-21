<?php
// add_item.php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
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


    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO brands (brand,address_1,address_2,telephone,mobile,agent,email,pec,vat,iban,sdi,payment)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$name,$adr1,$adr2,$tel,$mob,$agent,$email,$pec,$vat,$iban,$sdi,$payment]);
        $pdo->commit();
        header('Location: brand_list.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
