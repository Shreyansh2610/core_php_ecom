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
        echo "<br>".$brand;

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
    
} catch (PDOException $e) {
    echo "Connection failed or error creating table: " . $e->getMessage();
}
?>
