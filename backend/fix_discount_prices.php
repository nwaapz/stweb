<?php
/**
 * Fix Discount Prices
 * Calculate and update discount_price for products that have discount_percent but no discount_price
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$conn = getConnection();

// Get all products with discount_percent but no discount_price
$stmt = $conn->prepare("
    SELECT id, price, discount_percent, discount_price 
    FROM products 
    WHERE discount_percent IS NOT NULL 
    AND discount_percent > 0 
    AND (discount_price IS NULL OR discount_price = 0)
    AND price > 0
");

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$errors = [];

foreach ($products as $product) {
    $productId = $product['id'];
    $price = (float)$product['price'];
    $discountPercent = (float)$product['discount_percent'];
    
    // Calculate discount price
    $discountPrice = (int)round($price - ($price * $discountPercent / 100));
    
    if ($discountPrice > 0 && $discountPrice < $price) {
        // Update the product
        $updateStmt = $conn->prepare("
            UPDATE products 
            SET discount_price = ? 
            WHERE id = ?
        ");
        
        if ($updateStmt->execute([$discountPrice, $productId])) {
            $updated++;
            echo "Updated product ID {$productId}: Price={$price}, Discount={$discountPercent}%, New Price={$discountPrice}\n";
        } else {
            $errors[] = "Failed to update product ID {$productId}";
        }
    } else {
        $errors[] = "Invalid calculation for product ID {$productId}: discount_price would be {$discountPrice}";
    }
}

echo "\n=== Summary ===\n";
echo "Products updated: {$updated}\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
}

echo "\nDone!\n";
?>






