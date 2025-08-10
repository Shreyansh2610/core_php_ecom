<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
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
    <title>Home - Gestione fornitori e prodotti</title>
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
                        <a class="nav-link" href="home.php">Fornitori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_list.php">Prodotti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="brand_list.php">Marca</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Categorie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_orders.php">Visualizza ordini</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="suspendend_view_orders.php">Ordine sospeso</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2 class="mb-4">Benvenuto amministratore</h2>


        <div class="mb-3">
            <a href="order.php" class="btn btn-primary">➤ creare nuovo ordine</a>
            <a href="view_orders.php" class="btn btn-outline-secondary">📄 storico ordini</a>
        </div>

        <ul class="nav nav-tabs mb-3" id="homeTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="suppliers-tab" data-bs-toggle="tab" href="#suppliers" role="tab">Fornitori</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="items-tab" data-bs-toggle="tab" href="#items" role="tab">Prodotti</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="categories-tab" data-bs-toggle="tab" href="#categories" role="tab">Categorie</a>
            </li>
        </ul>

        <div class="tab-content" id="homeTabsContent">

            <!-- Supplier Management -->
            <div class="tab-pane fade show active" id="suppliers" role="tabpanel">
                <h4>Aggiungi Fornitore</h4>
                <form method="POST" action="add_supplier.php" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control mb-2" placeholder="Nome Fornitore" required>
                            <input type="text" name="address" class="form-control mb-2" placeholder="indirizzo">
                            <input type="text" name="phone" class="form-control mb-2" placeholder="telefono">
                            <input type="email" name="email" class="form-control mb-2" placeholder="E-mail dell'agente">
                            <input type="text" name="vat_number" class="form-control mb-2" placeholder="P.IVA">
                            <input type="text" name="sales_contact" class="form-control mb-2" placeholder="Rappresentante vendite (Agente)">
                            <input type="text" name="agent_telphone" class="form-control mb-2" placeholder="Telefono dell'agente">
                            <input type="text" name="sdi" class="form-control mb-2" placeholder="SDI">
                            <input type="text" name="iban" class="form-control mb-2" placeholder="IBAN">
                            <input type="text" name="supplier_email" class="form-control mb-2" placeholder="E-mail del fornitore">
                            <input type="text" name="supplier_email_pec" class="form-control mb-2" placeholder="Email fornitore pec">
                            <input type="text" name="supplier_cell" class="form-control mb-2" placeholder="Cellulare del fornitore">
                            <input type="text" name="supplier_responsible" class="form-control mb-2" placeholder="Responsabile">
                            <!-- <input type="text" name="payment" class="form-control mb-2" placeholder="Pagamento"> -->
                            <button type="submit" class="btn btn-success">Aggiungi Fornitore</button>
                        </div>
                    </div>
                </form>
                <hr>
                <h4>tutti i fornitori</h4>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>indirizzo</th>
                                <th>telefono</th>
                                <th>E-Mail</th>
                                <th>P.IVA</th>
                                <th>Rappresentante vendite</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
                            while ($row = $stmt->fetch()) {
                                echo "<tr>
                            <td>{$row['name']}</td>
                            <td>{$row['address']}</td>
                            <td>{$row['phone']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['vat_number']}</td>
                            <td>{$row['sales_contact']}</td>
                            <td>
                                <a href='edit_supplier.php?id={$row['id']}' class='btn btn-sm btn-warning'>Modificare</a>
                                <a href='delete_supplier.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this supplier?');\">Eliminare</a>
                            </td>
                        </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Item Management -->
            <div class="tab-pane fade" id="items" role="tabpanel">
                <h4>Aggiungi articolo</h4>
                <form action="add_item.php" method="POST" enctype="multipart/form-data">
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
                <a href="product_list.php" class=" mt-2 btn btn-outline-secondary">Tutto Prodotti</a>
                <hr>
                <h4>Tutto Prodotti</h4>
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
                            $stmt = $pdo->query("SELECT i.*, s.name AS supplier_name FROM items i JOIN suppliers s ON i.supplier_id = s.id ORDER BY i.name");
                            while ($item = $stmt->fetch()) {
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
                            ?>
                        </tbody>

                    </table>
                </div>
            </div>

            <!-- Category Management -->
            <div class="tab-pane fade" id="categories" role="tabpanel">
                <h4>Aggiungi categoria</h4>
                <form method="POST" action="add_category.php" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control mb-2" placeholder="Nome della categoria" required>
                            <button type="submit" class="btn btn-success">Aggiungi categoria</button>
                        </div>
                    </div>
                </form>

                <hr>
                <h4>All Categorie</h4>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                            while ($cat = $stmt->fetch()) {
                                echo "<tr>
                                <td>{$cat['id']}</td>
                                <td>{$cat['name']}</td>
                                <td>
                                    <a href='edit_category.php?id={$cat['id']}' class='btn btn-sm btn-warning'>Modificare</a>
                                    <a href='delete_category.php?id={$cat['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this category?');\">Eliminare</a>
                                </td>
                            </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

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