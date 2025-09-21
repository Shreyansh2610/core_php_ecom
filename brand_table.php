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


    $sql = "
        ALTER TABLE contact_details
ADD upload_time VARCHAR(50) NULL
    ";

    // Execute SQL
    $pdo->exec($sql);
    echo "Table 'contact_details' updated successfully!";

    $sql = "
    ALTER TABLE brands
    ADD address_1 VARCHAR(255) NULL,
    ADD address_2 VARCHAR(255) NULL,
    ADD telephone VARCHAR(50) NULL,
    ADD mobile VARCHAR(50) NULL,
    ADD agent VARCHAR(255) NULL,
    ADD email VARCHAR(255) NULL,
    ADD pec VARCHAR(255) NULL,
    ADD vat VARCHAR(255) NULL,
    ADD iban VARCHAR(255) NULL,
    ADD sdi VARCHAR(255) NULL,
    ADD payment VARCHAR(255) NULL
";


    // Execute SQL
    $pdo->exec($sql);
    echo "Table 'brands' updated successfully!";

} catch (PDOException $e) {
    echo "Connection failed or error creating table: " . $e->getMessage();
}
