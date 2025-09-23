<?php
// order.php
require 'config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
  header('Location: login.php');
  exit;
}

// Fetch suppliers
$suppliers = $pdo->query("SELECT id, name, address, phone, email, vat_number, sales_contact FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$brands = $pdo->query("SELECT id, brand FROM brands ORDER BY brand")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['success']) && $_GET['success'] == 1) {

  header('Location: view_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    function fetchBrands(supplierId) {
      // fetch(`get_brands_by_supplier.php?supplier_id=${supplierId}`)
      //   .then(res => res.json())
      //   .then(data => {
      //     const brandSelect = document.getElementById('brand');
      //     const itemList = document.getElementById('item_list');
      //     brandSelect.innerHTML = '<option value="">-- Select Brand --</option>';
      //     itemList.innerHTML = '';
      //     data.forEach(brand => {
      //       brandSelect.innerHTML += `<option value="${brand}">${brand}</option>`;
      //     });
      //   });
      // get_supplier_data_by_supplier

      // fetch(`get_supplier_data_by_supplier.php?supplier_id=${supplierId}`)
      //   .then(res => res.json())
      //   .then(data => {
      //     console.log(data)
      //     document.querySelector('[name="order_supplier_name"]').value = data['name'] ?? '';
      //     document.querySelector('[name="order_supplier_address"]').value = data['address'] ?? '';
      //     document.querySelector('[name="order_supplier_email"]').value = data['email'] ?? '';
      //     document.querySelector('[name="order_supplier_phone"]').value = data['phone'] ?? '';
      //     document.querySelector('[name="order_supplier_vat"]').value = data['vat_number'] ?? '';
      //     document.querySelector('[name="order_supplier_sales_contact"]').value = data['sales_contact'] ?? '';
      //   });
    }

    function fetchItemsByBrand(brandName) {
      fetch(`get_items_by_brand.php?brand=${encodeURIComponent(brandName)}`)
        .then(res => res.json())
        .then(data => {
          const itemList = document.getElementById('item_list');
          itemList.innerHTML = '';
          data.forEach(item => {
            const imageHtml = item.image ?
              `<img src="uploads/${encodeURIComponent(item.image)}" alt="Item Image" style="width: 60px; height: 60px;">` :
              `<span class="text-muted">Nessuna immagine</span>`;

            itemList.innerHTML += `
      <tr>
        <td>${imageHtml}</td>
            <td>${item.sku}</td>
            <td>${item.name}</td>
            <td>${item.units_per_box}</td>
            <td>
              <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(${item.id}, -1)">−</button>
                <input type="number" id="qty-${item.id}" name="quantities[${item.id}]" min="0" class="form-control text-center" value="0" />
                <button type="button" class="btn btn-outline-secondary" onclick="adjustQuantity(${item.id}, 1)">+</button>
              </div>
            </td>
          </tr>`;
          });
        });
    }
  </script>

  <script>
    function adjustQuantity(id, change) {
      const input = document.getElementById(`qty-${id}`);
      let value = parseInt(input.value || 0);
      value = Math.max(0, value + change);
      input.value = value;
    }
  </script>
  <style>
    .input-group {
      max-width: 160px;
    }

    .input-group .btn {
      width: 40px;
    }
  </style>



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
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <a href="view_orders.php" class="btn btn-outline-secondary">Visualizza ordini</a>
      <a href="./home.php" class="btn btn-secondary">Torna alla Home</a>
    </div>



    <h2>Crea ordine</h2>
    <form action="submit_order.php" method="POST">
      <div class="mb-3">

        <label for="supplier" class="form-label">Seleziona Commerciale</label>
        <select name="supplier_id" id="supplier" class="form-select" required onchange="fetchBrands(this.value)">
          <option value="">-- Seleziona Commerciale --</option>
          <?php foreach ($suppliers as $supplier): ?>
            <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- <div class="mb-3">
      <label class="form-label">Nome del fornitore</label>
      <input type="text" name="order_supplier_name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Indirizzo del fornitore</label>
      <textarea name="order_supplier_address" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Email del fornitore</label>
      <input type="email" name="order_supplier_email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Telefono del fornitore</label>
      <input type="text" name="order_supplier_phone" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Partita IVA del fornitore</label>
      <input type="text" name="order_supplier_vat" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Contatto vendite fornitore</label>
      <input type="text" name="order_supplier_sales_contact" class="form-control" required>
    </div> -->

      <div class="mb-3">
        <label for="brand" class="form-label">Seleziona azienda</label>
        <select name="brand" id="brand" class="form-select" required onchange="fetchItemsByBrand(this.value)">
          <option value="">-- Seleziona azienda --</option>
          <?php foreach ($brands as $brand): ?>
            <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['brand']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Data dell'ordine</label>
        <input type="date" name="order_date" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">numero dell'ordine</label>
        <input type="text" name="order_number" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Note</label>
        <textarea name="notes" class="form-control"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Invia ordine tramite</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="send_method" id="sendEmail" value="email" checked>
          <label class="form-check-label" for="sendEmail">E-mail</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="send_method" id="sendWhatsApp" value="whatsapp">
          <label class="form-check-label" for="sendWhatsApp">WhatsApp</label>
        </div>
      </div>


      <h5>Elementi</h5>
      <!-- <div class="table-responsive"> -->
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Immagine</th>
              <th>SKU</th>
              <th>Nome del prodotto</th>
              <th>Unità/Scatola</th>
              <th>Scatola richiesta</th>
            </tr>
          </thead>
          <tbody id="item_list"></tbody>
        </table>
      <!-- </div> -->
      <button type="submit" class="btn btn-primary my-5">Invia ordine</button>
    </form>
  </div>

</body>

</html>