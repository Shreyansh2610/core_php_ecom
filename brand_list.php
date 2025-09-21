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
    <title>Azienda</title>
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
                        <a class="nav-link" href="order.php">creare nuovo ordine</a>
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
            <h2 class="mb-0">Azienda</h2>
            <a href="./home.php" class="btn btn-secondary">Torna alla Home</a>
        </div>
        <h2 class="mb-2">Crea agenzia</h2>
        <form action="add_brand.php" method="POST" enctype="multipart/form-data" class="mb-2">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="brand" class="form-label">Nome</label>
                        <input type="text" name="brand" id="brand" class="form-control" placeholder="Azienda">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Indirizzo</label>
                        <input type="text" name="address" id="address" class="form-control" placeholder="Indirizzo">
                    </div>
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Telefono</label>
                        <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Telefono">
                    </div>
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Cellulare</label>
                        <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Cellulare">
                    </div>
                    <div class="mb-3">
                        <label for="address_1" class="form-label">Indirizzo 1</label>
                        <input type="text" name="address_1" id="address_1" class="form-control" placeholder="Indirizzo 1">
                    </div>
                    <div class="mb-3">
                        <label for="address_2" class="form-label">Indirizzo 2</label>
                        <input type="text" name="address_2" id="address_2" class="form-control" placeholder="Indirizzo 2">
                    </div>
                    <div class="mb-3">
                        <label for="agent" class="form-label">Responsabile Vendite (Agente)</label>
                        <input type="text" name="agent" id="agent" class="form-control" placeholder="Indirizzo 2">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" id="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="mb-3">
                        <label for="pec" class="form-label">PEC</label>
                        <input type="text" name="pec" id="pec" class="form-control" placeholder="PEC">
                    </div>
                    <div class="mb-3">
                        <label for="vat" class="form-label">P.Iva</label>
                        <input type="text" name="vat" id="vat" class="form-control" placeholder="P.Iva">
                    </div>
                    <div class="mb-3">
                        <label for="iban" class="form-label">IBAN</label>
                        <input type="text" name="iban" id="iban" class="form-control" placeholder="IBAN">
                    </div>
                    <div class="mb-3">
                        <label for="iban" class="form-label">SDI</label>
                        <input type="text" name="sdi" id="sdi" class="form-control" placeholder="SDI">
                    </div>
                    <div class="mb-3">
                        <label for="payment" class="form-label">Payment</label>
                        <input type="text" name="payment" id="payment" class="form-control" placeholder="Payment">
                    </div>
                    <button type="submit" class="btn btn-success">aggiungere agenzia</button>
                </div>
            </div>
        </form>

        <h2 class="mb-2">Tutte le agenzie</h2>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Azienda</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // $stmt = $pdo->query("SELECT i.*, s.name AS supplier_name FROM items i JOIN suppliers s ON i.supplier_id = s.id ORDER BY i.name");
                            $stmt = $pdo->prepare("
                        SELECT * 
                        FROM brands
                        ORDER BY created_at DESC
                    ");
                            $stmt->execute();
                            $brands = $stmt->fetchAll();

                            if (count($brands) > 0) {
                                foreach ($brands as $brand) {

                                    echo "<tr>
                                <td>{$brand['brand']}</td>
                                <td>
                                    <a href='edit_brand.php?id={$brand['id']}' class='btn btn-sm btn-warning'>Modificare</a>
                                    <a href='delete_brand.php?id={$brand['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this item?');\">Eliminare</a>
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
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>