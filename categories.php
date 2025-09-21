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
        <h2 class="mb-4">Benvenuto amministratore</h2>


        <div class="mb-3">
            <a href="order.php" class="btn btn-primary">➤ creare nuovo ordine</a>
            <a href="view_orders.php" class="btn btn-outline-secondary">📄 storico ordini</a>
        </div>

        <!-- <ul class="nav nav-tabs mb-3" id="homeTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="suppliers-tab" data-bs-toggle="tab" href="#suppliers" role="tab">Fornitori</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="items-tab" data-bs-toggle="tab" href="#items" role="tab">Prodotti</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="categories-tab" data-bs-toggle="tab" href="#categories" role="tab">Categorie</a>
            </li>
        </ul> -->

        <!-- Category Management -->
        <div id="categories">
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