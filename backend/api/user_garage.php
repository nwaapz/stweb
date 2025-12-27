<?php
/**
 * User Garage API
 * API گاراژ کاربر
 * 
 * Endpoints:
 * GET /api/user_garage.php              - Get all user vehicles
 * POST /api/user_garage.php             - Add a vehicle
 * DELETE /api/user_garage.php?id=123    - Remove a vehicle
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/user_functions.php';

// Check Authentication
// Uses checkUserAuth() from user_functions.php which handles 401 response
$user = checkUserAuth();

try {
    // $user is already set by checkUserAuth() above


    $method = $_SERVER['REQUEST_METHOD'];
    $conn = getConnection();

    if ($method === 'GET') {
        // Fetch user vehicles
        $stmt = $conn->prepare("
            SELECT uv.*, 
                   f.name as factory_name, 
                   f.logo as factory_logo,
                   v.name as vehicle_name
            FROM user_vehicles uv
            LEFT JOIN factories f ON uv.factory_id = f.id
            LEFT JOIN vehicles v ON uv.vehicle_id = v.id
            WHERE uv.user_id = ?
            ORDER BY uv.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $vehicles = $stmt->fetchAll();

        // Process images
        foreach ($vehicles as &$vehicle) {
            if ($vehicle['factory_logo']) {
                $vehicle['factory_logo_url'] = UPLOAD_URL . $vehicle['factory_logo'];
            }


            // Format display name
            if ($vehicle['vehicle_id']) {
                $vehicle['display_name'] = ($vehicle['year'] ? $vehicle['year'] . ' ' : '') . $vehicle['factory_name'] . ' ' . $vehicle['vehicle_name'];
            } else {
                $vehicle['display_name'] = ($vehicle['year'] ? $vehicle['year'] . ' ' : '') .
                    ($vehicle['custom_brand'] ?: 'Unknown') . ' ' .
                    ($vehicle['custom_model'] ?: '');
            }
        }

        echo json_encode(['success' => true, 'data' => $vehicles]);

    } elseif ($method === 'POST') {
        // Add new vehicle
        $data = json_decode(file_get_contents('php://input'), true);

        // Basic validation
        if (empty($data['vehicle_id']) && (empty($data['custom_brand']) || empty($data['custom_model']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'اطلاعات خودرو ناقص است']);
            exit;
        }

        $vehicleId = !empty($data['vehicle_id']) ? $data['vehicle_id'] : null;
        $factoryId = !empty($data['factory_id']) ? $data['factory_id'] : null;
        $customBrand = !empty($data['custom_brand']) ? $data['custom_brand'] : null;
        $customModel = !empty($data['custom_model']) ? $data['custom_model'] : null;
        $engine = !empty($data['engine']) ? $data['engine'] : null;
        $year = !empty($data['year']) ? $data['year'] : null;
        $vin = !empty($data['vin']) ? $data['vin'] : null;

        // If vehicle_id is provided, try to fill factory_id if missing
        if ($vehicleId && !$factoryId) {
            $stmt = $conn->prepare("SELECT factory_id FROM vehicles WHERE id = ?");
            $stmt->execute([$vehicleId]);
            $vData = $stmt->fetch();
            if ($vData)
                $factoryId = $vData['factory_id'];
        }

        $stmt = $conn->prepare("
            INSERT INTO user_vehicles 
            (user_id, vehicle_id, factory_id, custom_brand, custom_model, engine, year, vin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$user['id'], $vehicleId, $factoryId, $customBrand, $customModel, $engine, $year, $vin])) {
            echo json_encode(['success' => true, 'message' => 'خودرو با موفقیت افزوده شد', 'id' => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'خطا در ثبت خودرو']);
        }

    } elseif ($method === 'DELETE') {
        // Remove vehicle
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'شناسه خودرو الزامی است']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM user_vehicles WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'خودرو حذف شد']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'خودرو یافت نشد']);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'خطای سرور: ' . $e->getMessage()]);
}
