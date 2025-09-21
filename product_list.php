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
$brands = $pdo->query("SELECT DISTINCT brand FROM items WHERE brand IS NOT NULL AND brand != '' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);

// Fetch sku list for dropdown
$skus = $pdo->query("SELECT DISTINCT sku FROM items WHERE sku IS NOT NULL AND sku != '' ORDER BY sku")->fetchAll(PDO::FETCH_COLUMN);

// Handle filters
$supplier_id = $_GET['supplier_id'] ?? '';
$brand = $_GET['brand'] ?? '';
$sku = $_GET['sku'] ?? '';
$filterProduct = $_GET['product'] ?? '';

$whereClauses = [];
$params = [];

if (!empty($supplier_id)) {
    $whereClauses[] = 'i.supplier_id = ?';
    $params[] = $supplier_id;
}

if (!empty($brand)) {
    $whereClauses[] = 'i.brand = ?';
    $params[] = $brand;
}
if (!empty($sku)) {
    $whereClauses[] = 'i.sku = ?';
    $params[] = $sku;
}
if (!empty($filterProduct)) {
    $whereClauses[] = 'i.name LIKE %?%';
    $params[] = $filterProduct;
}

$whereSQL = '';
if ($whereClauses) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}
?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutto Prodotti</title>
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
                        <a class="nav-link" href="home.php">Commerciale</a>
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
            <h2 class="mb-0">Tutto Prodotti</h2>
            <a href="./home.php" class="btn btn-secondary">Torna alla Home</a>
        </div>
        <h2 class="mb-2">Aggiungi prodotto</h2>
        <form action="add_item.php" method="POST" enctype="multipart/form-data" class="mb-2">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control mb-2" placeholder="nome prodotto" required>
                    <input type="text" name="sku" class="form-control mb-2" placeholder="SKU">
                    <select name="brand" class="form-control mb-2" required>
                        <option value="">-- Seleziona Marca --</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, brand FROM brands ORDER BY brand");
                        while ($row = $stmt->fetch()) {
                            echo "<option value='{$row['id']}'>{$row['brand']}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="units_per_box" class="form-control mb-2" placeholder="Unità per scatola" required>
                    <select name="supplier_id" class="form-control mb-2" required>
                        <option value="">-- seleziona fornitore --</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, name FROM suppliers ORDER BY name");
                        while ($row = $stmt->fetch()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <select multiple name="category_ids[]" class="form-control mb-2">
                        <option disabled>-- seleziona Categorie (hold Ctrl) --</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
                        while ($row = $stmt->fetch()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                    <div class="mb-3">
                        <label for="image" class="form-label">Immagine del prodotto</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-success">aggiungi prodotto</button>
                </div>
            </div>
        </form>

        <h2 class="mb-2">Tutto Prodotti</h2>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
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
                        <option value="<?= htmlspecialchars($b) ?>" <?= $brand == $b ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sku" class="form-label">Codice articolo</label>
                <select name="sku" id="sku" class="form-select">
                    <option value="">Seleziona il codice articolo</option>
                    <?php foreach ($skus as $b): ?>
                        <option value="<?= htmlspecialchars($b) ?>" <?= $sku == $b ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="product" class="form-label">Nome</label>
                <input type="text" name="product" id="product" class="form-control" value="<?= htmlspecialchars($filterProduct) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtro</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Immagine</th>
                        <th>Nome</th>
                        <th>SKU</th>
                        <th>Marca</th>
                        <th>Unità/Scatola</th>
                        <th>Fornitore</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // $stmt = $pdo->query("SELECT i.*, s.name AS supplier_name FROM items i JOIN suppliers s ON i.supplier_id = s.id ORDER BY i.name");
                    $stmt = $pdo->prepare("
                        SELECT i.*, s.name AS supplier_name 
                        FROM items i
                        JOIN suppliers s ON i.supplier_id = s.id
                        $whereSQL
                        ORDER BY i.name
                    ");
                    $stmt->execute($params);
                    $items = $stmt->fetchAll();

                    if (count($items) > 0) {
                        foreach ($items as $item) {
                            $imageHtml = $item['image']
                                ? "<img src='uploads/" . htmlspecialchars($item['image']) . "' alt='Item Image' style='max-width: 60px; max-height: 60px;'>"
                                : "<span class='text-muted'>Nessuna immagine</span>";

                            echo "<tr>
                                <td>{$imageHtml}</td>
                                <td>{$item['name']}</td>
                                <td>{$item['sku']}</td>
                                <td>{$item['brand']}</td>
                                <td>{$item['units_per_box']}</td>
                                <td>{$item['supplier_name']}</td>
                                <td>
                                    <a href='edit_item.php?id={$item['id']}' class='btn btn-sm btn-warning'>Modificare</a>
                                    <a href='delete_item.php?id={$item['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this item?');\">Eliminare</a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo '<tr><td colspan="100%" class="text=center">Nessun record trovato!</td></tr>';
                    }
                    ?>
                </tbody>

            </table>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const triggerEl = document.querySelector(`a[data-bs-toggle="tab"][href="#${tab}"]`);
                if (triggerEl) {
                    const tabInstance = new bootstrap.Tab(triggerEl);
                    tabInstance.show();
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>