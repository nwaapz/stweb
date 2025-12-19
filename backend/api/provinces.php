<?php
/**
 * Provinces API
 * API استان‌ها
 * 
 * Endpoints:
 * GET /api/provinces.php                - Get all provinces
 * GET /api/provinces.php?id=1           - Get single province
 * GET /api/provinces.php?slug=tehran    - Get province by slug
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();

    // Single province by slug
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];
        $province = getProvinceBySlug($slug);

        if (!$province) {
            http_response_code(404);
            echo json_encode(['error' => 'استان یافت نشد', 'success' => false]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $province
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Single province by ID
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $province = getProvinceById($id);

        if (!$province) {
            http_response_code(404);
            echo json_encode(['error' => 'استان یافت نشد', 'success' => false]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $province
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // All provinces (active only by default)
    $filters = [];
    if (!isset($_GET['include_inactive'])) {
        $filters['is_active'] = 1;
    }

    $provinces = getProvinces($filters);

    echo json_encode([
        'success' => true,
        'count' => count($provinces),
        'data' => $provinces
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}
?>