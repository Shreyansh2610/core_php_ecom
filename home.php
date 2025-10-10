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
                        <a class="nav-link" href="order.php">Creare nuovo ordine</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commercial.php">Commerciale</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_list.php">Prodotti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="brand_list.php">Aziende</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_orders.php">Visualizza ordini</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="suspendend_view_orders.php">Ordini sospesi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_contact.php?id=1">Contatto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- <div class="container mt-4">

        <div class="tab-pane fade show active" id="suppliers" role="tabpanel">
            <h4>Aggiungi Commerciale</h4>
            <form method="POST" action="add_supplier.php" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Nome Commerciale" required>
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
                        <button type="submit" class="btn btn-success">Aggiungi Commerciale</button>
                    </div>
                </div>
            </form>
            <hr>
            <h4>tutti i fornitori</h4>
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
    </div> -->
    <div class="my-5">
        <div class="row mx-3">
            <div class="col-md-4 mx-auto my-3">
                <a href="commercial.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Commerciale</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mx-auto my-3">
                <a href="product_list.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Prodotti</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mx-auto my-3">
                <a href="brand_list.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Aziende</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mx-auto my-3">
                <a href="order.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Crea Nuovo Ordine</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mx-auto my-3">
                <a href="view_orders.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Visualizza ordini</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mx-auto my-3">
                <a href="suspendend_view_orders.php" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Ordini sospesi</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mx-auto my-3">
                <a href="edit_contact.php?id=1" class="text-decoration-none">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Contatto</h5>
                        </div>
                    </div>
                </a>
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