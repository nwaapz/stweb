<?php
/**
 * Branches API
 * API شعب
 * 
 * Endpoints:
 * GET /api/branches.php?province_id=1        - Get branches by province ID
 * GET /api/branches.php?province_slug=tehran - Get branches by province slug
 * GET /api/branches.php                       - Get all branches
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();

    // Get branches by province slug
    if (isset($_GET['province_slug'])) {
        $slug = $_GET['province_slug'];
        $province = getProvinceBySlug($slug);

        if (!$province) {
            http_response_code(404);
            echo json_encode(['error' => 'استان یافت نشد', 'success' => false]);
            exit;
        }

        $branches = getBranchesByProvince($province['id']);

        echo json_encode([
            'success' => true,
            'province' => $province,
            'count' => count($branches),
            'data' => $branches
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get branches by province ID
    if (isset($_GET['province_id'])) {
        $provinceId = (int) $_GET['province_id'];
        $province = getProvinceById($provinceId);

        if (!$province) {
            http_response_code(404);
            echo json_encode(['error' => 'استان یافت نشد', 'success' => false]);
            exit;
        }

        $branches = getBranchesByProvince($provinceId);

        echo json_encode([
            'success' => true,
            'province' => $province,
            'count' => count($branches),
            'data' => $branches
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // All branches (active only by default)
    $filters = [];
    if (!isset($_GET['include_inactive'])) {
        $filters['is_active'] = 1;
    }

    $branches = getBranches($filters);

    echo json_encode([
        'success' => true,
        'count' => count($branches),
        'data' => $branches
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}
?>