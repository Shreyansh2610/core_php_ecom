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
    'canceled' => 'annullato'
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
        <button type="submit" class="btn btn-primary">Aggiorna Stato</button>
        <a href="view_orders.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
