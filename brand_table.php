<?php

$host = 'localhost';
$db   = 'u567802240_kdker';
// $user = 'u567802240_kdker';
// $pass = 'SdtXLH7{]c';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully!<br>";

    // SQL to create table
    $sql = "
        CREATE TABLE IF NOT EXISTS brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            brand VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";

    // Execute SQL
    $pdo->exec($sql);
    echo "Table 'brands' created successfully!";

    $brands = $pdo->query("SELECT DISTINCT brand FROM items WHERE brand IS NOT NULL AND brand != '' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($brands as $key => $brand) {
        echo "<br>" . $brand;

        // Insert brand if not exists
        $stmt = $pdo->prepare("INSERT IGNORE INTO brands (brand) VALUES (:brand)");
        $stmt->execute(['brand' => $brand]);

        // Get the corresponding brand ID
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE brand = :brand LIMIT 1");
        $stmt->execute(['brand' => $brand]);
        $brand_id = $stmt->fetchColumn();

        // Update items table to set brand_id
        $stmt = $pdo->prepare("UPDATE items SET brand = :brand_id WHERE brand = :brand");
        $stmt->execute([
            'brand_id' => $brand_id,
            'brand'    => $brand
        ]);
    }

    $sql = "
        ALTER TABLE orders MODIFY status ENUM('pending','confirmed','complete','canceled', 'suspended') NOT NULL
    ";

    // Execute SQL
    $pdo->exec($sql);
    echo "Table 'orders' updated successfully!";

    $sql = "
        ALTER TABLE orders 
ADD supplier_name VARCHAR(255) NULL AFTER status,
ADD supplier_address TEXT NULL AFTER supplier_name,
ADD supplier_phone VARCHAR(20) NULL AFTER supplier_address,
ADD supplier_email VARCHAR(100) NULL AFTER supplier_phone,
ADD supplier_vat_number VARCHAR(50) NULL AFTER supplier_email,
ADD supplier_sales_contact VARCHAR(255) NULL AFTER supplier_vat_number;
    ";

    // Execute SQL
    $pdo->exec($sql);
    echo "Table 'orders' updated successfully!";

    $sql = "UPDATE orders o
JOIN suppliers s ON o.supplier_id = s.id
SET o.supplier_name = s.name,o.supplier_address = s.address,o.supplier_phone = s.phone,o.supplier_email = s.email,o.supplier_vat_number = s.vat_number,o.supplier_sales_contact = s.sales_contact;
";
// Execute SQL
    $pdo->exec($sql);
    echo "Table 'orders' updated successfully!";


    $sql = "
        ALTER TABLE suppliers
ADD agent_telphone VARCHAR(20) NULL AFTER sales_contact,
ADD sdi VARCHAR(50) NULL AFTER agent_telphone,
ADD iban TEXT NULL AFTER sdi,
ADD supplier_email VARCHAR(255) NULL AFTER iban,
ADD supplier_email_pec VARCHAR(255) NULL AFTER supplier_email,
ADD supplier_cell VARCHAR(20) NULL AFTER supplier_email_pec,
ADD supplier_responsible VARCHAR(255) NULL AFTER supplier_cell,
ADD payment VARCHAR(255) NULL AFTER supplier_responsible
    ";

    // Execute SQL
    $pdo->exec($sql);
    echo "Table 'suppliers' updated successfully!";

} catch (PDOException $e) {
    echo "Connection failed or error creating table: " . $e->getMessage();
}
