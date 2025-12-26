<?php
/**
 * Products API
 * API محصولات
 * 
 * Endpoints:
 * GET /api/products.php                    - Get all products
 * GET /api/products.php?id=1               - Get single product
 * GET /api/products.php?category=1         - Get products by category
 * GET /api/products.php?vehicle=1         - Get products by vehicle
 * GET /api/products.php?featured=1         - Get featured products
 * GET /api/products.php?discounted=1       - Get discounted products
 * GET /api/products.php?search=keyword     - Search products
 * GET /api/products.php?price_min=500      - Minimum price filter
 * GET /api/products.php?price_max=1000     - Maximum price filter
 * GET /api/products.php?limit=10           - Limit results
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();

    // Single product by Slug
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];
        $product = getProductBySlug($slug);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'محصول یافت نشد', 'success' => false]);
            exit;
        }

        // Increment views
        $id = $product['id'];
        $stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);

        // Calculate discount_price from discount_percent if discount_price is NULL but discount_percent exists
        // This should happen BEFORE checking hasActiveDiscount
        if ((empty($product['discount_price']) || $product['discount_price'] == 0) && !empty($product['discount_percent']) && !empty($product['price'])) {
            $numericPrice = (float)$product['price'];
            $discountPercent = (float)$product['discount_percent'];
            $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
            if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                $product['discount_price'] = $calculatedPrice;
            }
        }

        // Format response
        $product['has_discount'] = hasActiveDiscount($product);
        $product['effective_price'] = getEffectivePrice($product);
        $product['formatted_price'] = formatPrice($product['price']);
        $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
        $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;
        $product['rating'] = $product['rating'] ?? 0;
        $product['reviews'] = $product['reviews'] ?? 0;


        echo json_encode([
            'success' => true,
            'data' => $product
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Single product by ID
    if (isset($_GET['id'])) {
        $idParam = $_GET['id'];

        if (is_numeric($idParam)) {
            $id = (int) $idParam;
            $product = getProductById($id);
        } else {
            // Get product by name
            $stmt = $conn->prepare("
                SELECT p.*, c.name as category_name, v.name as vehicle_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vehicles v ON p.vehicle_id = v.id 
                WHERE p.name = ?
            ");
            $stmt->execute([$idParam]);
            $product = $stmt->fetch();

            if ($product) {
                $id = $product['id'];
            }
        }

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'محصول یافت نشد', 'success' => false]);
            exit;
        }

        // Increment views
        $stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);

        // Calculate discount_price from discount_percent if discount_price is NULL but discount_percent exists
        // This should happen BEFORE checking hasActiveDiscount
        if ((empty($product['discount_price']) || $product['discount_price'] == 0) && !empty($product['discount_percent']) && !empty($product['price'])) {
            $numericPrice = (float)$product['price'];
            $discountPercent = (float)$product['discount_percent'];
            $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
            if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                $product['discount_price'] = $calculatedPrice;
            }
        }

        // Format response
        $product['has_discount'] = hasActiveDiscount($product);
        $product['effective_price'] = getEffectivePrice($product);
        $product['formatted_price'] = formatPrice($product['price']);
        
        // Always return formatted_discount_price if discount_percent exists, even if discount isn't active yet
        if (!empty($product['discount_percent']) && !empty($product['price'])) {
            // If discount_price wasn't calculated above, calculate it now
            if (empty($product['discount_price']) || $product['discount_price'] == 0) {
                $numericPrice = (float)$product['price'];
                $discountPercent = (float)$product['discount_percent'];
                $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
                if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                    $product['discount_price'] = $calculatedPrice;
                }
            }
            // Format the discount price
            if ($product['discount_price'] && $product['discount_price'] > 0) {
                $product['formatted_discount_price'] = formatPrice($product['discount_price']);
            } else {
                $product['formatted_discount_price'] = null;
            }
        } else {
            $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
        }
        
        $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;


        echo json_encode([
            'success' => true,
            'data' => $product
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Build filters
    $filters = [];

    if (isset($_GET['category'])) {
        $filters['category_id'] = (int) $_GET['category'];
    }

    if (isset($_GET['category_name'])) {
        $filters['category_name'] = $_GET['category_name'];
    }

    if (isset($_GET['vehicle'])) {
        $filters['vehicle_id'] = (int) $_GET['vehicle'];
    }

    if (isset($_GET['featured'])) {
        $filters['is_featured'] = true;
    }

    if (isset($_GET['discounted'])) {
        $filters['has_discount'] = true;
    }

    if (isset($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }

    if (isset($_GET['price_min'])) {
        $filters['price_min'] = (float) $_GET['price_min'];
    }

    if (isset($_GET['price_max'])) {
        $filters['price_max'] = (float) $_GET['price_max'];
    }

    if (isset($_GET['limit'])) {
        $filters['limit'] = (int) $_GET['limit'];
    }

    if (isset($_GET['offset'])) {
        $filters['offset'] = (int) $_GET['offset'];
    }

    // Order by
    if (isset($_GET['order_by'])) {
        $filters['order_by'] = $_GET['order_by'];
    }

    if (isset($_GET['order_dir'])) {
        $filters['order_dir'] = $_GET['order_dir'];
    }

    // Popular products (ordered by views)
    if (isset($_GET['popular'])) {
        $filters['order_by'] = 'views';
        $filters['order_dir'] = 'DESC';
    }

    // Only active products for API (unless debug mode)
    if (!isset($_GET['include_inactive'])) {
        $filters['is_active'] = 1;
    }

    // Get total count before applying limit/offset
    $totalCount = getProductsCount($filters);
    
    // Calculate price range from products that match ALL current filters (including price filter)
    // This gives the actual min/max of products that are currently being displayed/filtered
    $filtersForRange = $filters;
    unset($filtersForRange['limit']);
    unset($filtersForRange['offset']);
    
    // Get all products matching current filters (including price filter)
    $allMatchingProducts = getProducts($filtersForRange);
    
    // Calculate price range from all matching products after price calculations
    $priceRange = ['min' => 0, 'max' => 1000];
    try {
        if (!empty($allMatchingProducts)) {
            $prices = [];
            foreach ($allMatchingProducts as $product) {
                // Calculate discount_price from discount_percent if needed
                if ((empty($product['discount_price']) || $product['discount_price'] == 0) && !empty($product['discount_percent']) && !empty($product['price'])) {
                    $numericPrice = (float)$product['price'];
                    $discountPercent = (float)$product['discount_percent'];
                    $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
                    if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                        // Check if discount is active
                        $discountActive = true;
                        if (!empty($product['discount_start']) && strtotime($product['discount_start']) > time()) {
                            $discountActive = false;
                        }
                        if (!empty($product['discount_end']) && strtotime($product['discount_end']) < time()) {
                            $discountActive = false;
                        }
                        if ($discountActive) {
                            $product['discount_price'] = $calculatedPrice;
                        }
                    }
                }
                
                // Get effective price (discount_price if active, else price)
                $effectivePrice = 0;
                if (function_exists('getEffectivePrice')) {
                    $effectivePrice = getEffectivePrice($product);
                } else {
                    // Fallback: use discount_price if it exists and is active, otherwise use price
                    if (!empty($product['discount_price']) && $product['discount_price'] > 0) {
                        // Check if discount is active
                        $discountActive = true;
                        if (!empty($product['discount_start']) && strtotime($product['discount_start']) > time()) {
                            $discountActive = false;
                        }
                        if (!empty($product['discount_end']) && strtotime($product['discount_end']) < time()) {
                            $discountActive = false;
                        }
                        if ($discountActive) {
                            $effectivePrice = (float)$product['discount_price'];
                        } else {
                            $effectivePrice = (float)($product['price'] ?? 0);
                        }
                    } else {
                        $effectivePrice = (float)($product['price'] ?? 0);
                    }
                }
                
                if ($effectivePrice > 0 && is_numeric($effectivePrice)) {
                    $prices[] = $effectivePrice;
                }
            }
            
            if (!empty($prices)) {
                $priceRange = [
                    'min' => floor(min($prices)),
                    'max' => ceil(max($prices))
                ];
                // If min equals max (only one product), ensure we have a valid range
                if ($priceRange['min'] == $priceRange['max']) {
                    // Keep the same value for both
                    $priceRange['max'] = $priceRange['min'];
                }
            }
        }
    } catch (Exception $e) {
        // If price range calculation fails, use default range
        error_log('Error calculating price range: ' . $e->getMessage());
        $priceRange = ['min' => 0, 'max' => 1000];
    }
    
    // Get products for current page (with limit/offset)
    $products = getProducts($filters);

    // Format products
    foreach ($products as &$product) {
        try {
            // Calculate discount_price from discount_percent if discount_price is NULL but discount_percent exists
            // This should happen BEFORE checking hasActiveDiscount
            if ((empty($product['discount_price']) || $product['discount_price'] == 0) && !empty($product['discount_percent']) && !empty($product['price'])) {
                $numericPrice = (float)$product['price'];
                $discountPercent = (float)$product['discount_percent'];
                $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
                if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                    $product['discount_price'] = $calculatedPrice;
                }
            }
            
            // Safely call functions with fallbacks
            if (function_exists('hasActiveDiscount')) {
                $product['has_discount'] = hasActiveDiscount($product);
            } else {
                $product['has_discount'] = !empty($product['discount_price']) && $product['discount_price'] > 0;
            }
            
            if (function_exists('getEffectivePrice')) {
                $product['effective_price'] = getEffectivePrice($product);
            } else {
                $product['effective_price'] = $product['discount_price'] ?? $product['price'] ?? 0;
            }
            
            if (function_exists('formatPrice')) {
                $product['formatted_price'] = formatPrice($product['price']);
            } else {
                $product['formatted_price'] = number_format($product['price'] ?? 0, 0, '.', ',');
            }
        
        // Always return formatted_discount_price if discount_percent exists, even if discount isn't active yet
        if (!empty($product['discount_percent']) && !empty($product['price'])) {
            // If discount_price wasn't calculated above, calculate it now
            if (empty($product['discount_price']) || $product['discount_price'] == 0) {
                $numericPrice = (float)$product['price'];
                $discountPercent = (float)$product['discount_percent'];
                $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
                if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                    $product['discount_price'] = $calculatedPrice;
                }
            }
            // Format the discount price
            if ($product['discount_price'] && $product['discount_price'] > 0) {
                $product['formatted_discount_price'] = formatPrice($product['discount_price']);
            } else {
                $product['formatted_discount_price'] = null;
            }
        } else {
            if (function_exists('formatPrice')) {
                $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
            } else {
                $product['formatted_discount_price'] = $product['discount_price'] ? number_format($product['discount_price'], 0, '.', ',') : null;
            }
        }
        $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;
        $product['rating'] = $product['rating'] ?? 0;
        $product['reviews'] = $product['reviews'] ?? 0;

        // Vehicle info is already included from the JOIN in getProducts()
        } catch (Exception $e) {
            // If formatting fails for a product, log and continue
            error_log('Error formatting product ' . ($product['id'] ?? 'unknown') . ': ' . $e->getMessage());
            // Set default values
            $product['has_discount'] = false;
            $product['effective_price'] = $product['price'] ?? 0;
            $product['formatted_price'] = number_format($product['price'] ?? 0, 0, '.', ',');
            $product['formatted_discount_price'] = null;
            $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;
            $product['rating'] = $product['rating'] ?? 0;
            $product['reviews'] = $product['reviews'] ?? 0;
        }
    }

    echo json_encode([
        'success' => true,
        'count' => count($products),
        'total' => $totalCount,
        'price_range' => $priceRange, // Add min/max prices for filtered products
        'data' => $products
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}
?>