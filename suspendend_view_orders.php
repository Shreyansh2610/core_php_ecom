<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fetch supplier list for dropdown
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch brand list for dropdown
$brands = $pdo->query("SELECT * FROM brands ORDER BY brand")->fetchAll();

// Handle filters
$supplier_id = $_GET['supplier_id'] ?? '';
$brand = $_GET['brand'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status = $_GET['status'] ?? '';

$whereClauses = [];
$params = [];

if (!empty($supplier_id)) {
    $whereClauses[] = 'o.supplier_id = ?';
    $params[] = $supplier_id;
}

if (!empty($brand)) {
    $whereClauses[] = 'i.brand = ?';
    $params[] = $brand;
}

if (!empty($date_from)) {
    $whereClauses[] = 'o.order_date >= ?';
    $params[] = $date_from;
}
if (!empty($date_to)) {
    $whereClauses[] = 'o.order_date <= ?';
    $params[] = $date_to;
}

$whereClauses[] = 'o.status = ?';
$params[] = "suspended";

$whereSQL = '';
if ($whereClauses) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Fetch orders with optional filters
$stmt = $pdo->prepare("
    SELECT DISTINCT o.*, s.name AS supplier_name
    FROM orders o
    LEFT JOIN suppliers s ON o.supplier_id = s.id
    JOIN order_items oi ON oi.order_id = o.id
    JOIN items i ON oi.item_id = i.id
    $whereSQL
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();
$statusOptions = [
    'pending' => 'in attesa',
    'confirmed' => 'confermato',
    'complete' => 'completato',
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ordine sospeso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">Benvenuto amministratore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order.php">Creare nuovo ordine</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commercial.php">Commerciale</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_list.php">Prodotti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="brand_list.php">Azienda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Categorie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_orders.php">Visualizza ordini</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="suspendend_view_orders.php">Ordine sospeso</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_contact.php?id=1">Contatto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Ordine sospeso</h2>
        <a href="./home.php" class="btn btn-secondary">Torna alla Home</a>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label for="supplier_id" class="form-label">Fornitore</label>
            <select name="supplier_id" id="supplier_id" class="form-select">
                <option value="">Tutti i fornitori</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $supplier_id == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="brand" class="form-label">Marca</label>
            <select name="brand" id="brand" class="form-select">
                <option value="">Tutti i marchi</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $brand == $b['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['brand']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="col-md-2">
            <label for="date_from" class="form-label">Da</label>
            <input type="date" name="date_from" id="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
        </div>

        <div class="col-md-2">
            <label for="date_to" class="form-label">A</label>
            <input type="date" name="date_to" id="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtro</button>
        </div>
    </form>

    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
            <?php
            $statusColors = [
                'pending' => 'warning',
                'confirmed' => 'info',
                'complete' => 'success',
                'canceled' => 'danger'
            ];
            $status = $order['status'] ?? 'pending';
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Ordine #<?= htmlspecialchars($order['order_number']) ?></strong>
                        | Fornitori: <?= htmlspecialchars($order['supplier_name']) ?>
                        | Data: <?= htmlspecialchars($order['order_date']) ?>
                        | Inviato tramite: <?= htmlspecialchars($order['sent_method'] ?? 'N/A') ?>
                        <span class="badge bg-<?= $statusColors[$status] ?? 'secondary' ?>">
                            <?= ucfirst($statusOptions[$status] ?? $status) ?>
                        </span>
                    </div>
                    <a href="edit_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                        Modificare
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($order['pdf_filename'])): ?>
                        <p><a href="./orders_pdf/<?= htmlspecialchars($order['pdf_filename']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            Visualizza PDF
                        </a></p>
                    <?php endif; ?>

                    <?php if (!empty($order['notes'])): ?>
                        <p><strong>Note:</strong> <?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                    <?php endif; ?>

                    <table class="table table-sm table-bordered">
                        <thead>
                        <tr>
                            <th>Articolo</th>
                            <th>SKU</th>
                            <!-- <th>Marca</th> -->
                            <th>Unità/Scatola</th>
                            <th>Scatole ordinate</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $orderId = $order['id'];
                        $itemStmt = $pdo->prepare("SELECT oi.box_requested, i.name, i.sku, i.brand, i.units_per_box
                                                   FROM order_items oi
                                                   JOIN items i ON oi.item_id = i.id
                                                   WHERE oi.order_id = ?");
                        $itemStmt->execute([$orderId]);
                        $items = $itemStmt->fetchAll();

                        foreach ($items as $item):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['sku']) ?></td>
                                <!-- <td><?= htmlspecialchars($item['brand']) ?></td> -->
                                <td><?= htmlspecialchars($item['units_per_box']) ?></td>
                                <td><?= htmlspecialchars($item['box_requested']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nessun ordine trovato.</p>
    <?php endif; ?>
</div>
</body>
</html>
