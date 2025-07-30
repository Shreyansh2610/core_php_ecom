<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB connection
$pdo = new PDO("mysql:host=localhost;dbname=u567802240_kdker", "u567802240_kdker", "SdtXLH7{]c");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Function to download image and return filename
function downloadImage($url, $uploadDir = "uploads/") {
    $url = trim($url);
    if (!$url) return null;

    $filename = basename(parse_url($url, PHP_URL_PATH));
    $savePath = $uploadDir . $filename;

    // Create uploads directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Only download if not already present
    if (!file_exists($savePath)) {
        $imageContent = @file_get_contents($url);
        if ($imageContent !== false) {
            file_put_contents($savePath, $imageContent);
        } else {
            return null; // Could not download
        }
    }

    return $filename;
}

// Load CSV
$csv = fopen("products.csv", "r");
if (!$csv) {
    die("CSV file not found.");
}

$header = fgetcsv($csv); // Skip header row

while (($row = fgetcsv($csv)) !== false) {
    [$sku, $name, $categoryStr, $imageUrl, $brand, $boxMeta] = $row;

    $sku = trim($sku);
    $name = trim($name);
    $brand = trim($brand);
    $unitsPerBox = trim($boxMeta);

    if (!$name || !$brand) continue;

    // 1. Ensure supplier exists
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ?");
    $stmt->execute([$brand]);
    $supplier_id = $stmt->fetchColumn();

    if (!$supplier_id) {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name) VALUES (?)");
        $stmt->execute([$brand]);
        $supplier_id = $pdo->lastInsertId();
    }

    // 2. Download image and get filename
    $image = downloadImage($imageUrl); // will return just the filename

    // 3. Insert item
    $stmt = $pdo->prepare("INSERT INTO items (supplier_id, name, sku, brand, units_per_box, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$supplier_id, $name, $sku, $brand, $unitsPerBox, $image]);
    $item_id = $pdo->lastInsertId();

    // 4. Process and insert categories
    $categories = array_map('trim', preg_split('/[,>]/', $categoryStr));
    foreach ($categories as $cat) {
        if (!$cat) continue;

        // Check if category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$cat]);
        $cat_id = $stmt->fetchColumn();

        if (!$cat_id) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$cat]);
            $cat_id = $pdo->lastInsertId();
        }

        // Link item to category
        $stmt = $pdo->prepare("INSERT IGNORE INTO item_categories (item_id, category_id) VALUES (?, ?)");
        $stmt->execute([$item_id, $cat_id]);
    }
}

fclose($csv);
echo "✅ Import completed successfully.";
?>
