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
    <title>Tutti i marchi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Tutto Marca</h2>
            <a href="./home.php" class="btn btn-secondary">Torna alla Home</a>
        </div>
        <h2 class="mb-2">Aggiungi prodotto</h2>
        <form action="add_brand.php" method="POST" enctype="multipart/form-data" class="mb-2">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marca</label>
                            <input type="text" name="brand" id="brand" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success">Aggiungi marchio</button>
                    </div>
                </div>
            </form>

        <h2 class="mb-2">Tutti i marchi</h2>


        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Marca</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // $stmt = $pdo->query("SELECT i.*, s.name AS supplier_name FROM items i JOIN suppliers s ON i.supplier_id = s.id ORDER BY i.name");
                    $stmt = $pdo->prepare("
                        SELECT * 
                        FROM brands
                        ORDER BY created_at
                    ");
                    $stmt->execute();
                    $brands = $stmt->fetchAll();

                    if (count($brands) > 0) {
                        foreach ($brands as $brand) {

                            echo "<tr>
                                <td>{$brand['brand']}</td>
                                <td>
                                    <a href='edit_item.php?id={$brand['id']}' class='btn btn-sm btn-warning'>Modificare</a>
                                    <a href='delete_item.php?id={$brand['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this item?');\">Eliminare</a>
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
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>