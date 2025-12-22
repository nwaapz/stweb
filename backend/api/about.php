<?php
/**
 * About Page API
 * API صفحه درباره ما
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    $response = ['success' => true];
    
    // Get main about page content
    $stmt = $conn->query("SELECT * FROM about_page WHERE is_active = 1 LIMIT 1");
    $aboutPage = $stmt->fetch();
    
    if ($aboutPage) {
        // Convert image path to full URL
        if (!empty($aboutPage['feature_image'])) {
            $aboutPage['feature_image_url'] = UPLOAD_URL . $aboutPage['feature_image'];
        }
        $response['about'] = $aboutPage;
    }
    
    // Get team members
    $stmt = $conn->query("SELECT id, name, position, description, image, sort_order FROM about_team WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $teamMembers = $stmt->fetchAll();
    
    foreach ($teamMembers as &$member) {
        if (!empty($member['image'])) {
            $member['image_url'] = UPLOAD_URL . $member['image'];
        }
    }
    $response['team'] = $teamMembers;
    
    // Get testimonials
    $stmt = $conn->query("SELECT id, text, author_name, author_title, rating, avatar, sort_order FROM about_testimonials WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $testimonials = $stmt->fetchAll();
    
    foreach ($testimonials as &$testimonial) {
        if (!empty($testimonial['avatar'])) {
            $testimonial['avatar_url'] = UPLOAD_URL . $testimonial['avatar'];
        }
    }
    $response['testimonials'] = $testimonials;
    
    // Get statistics
    $stmt = $conn->query("SELECT id, value, title, sort_order FROM about_statistics WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $statistics = $stmt->fetchAll();
    $response['statistics'] = $statistics;
    
    $response['count'] = [
        'team' => count($teamMembers),
        'testimonials' => count($testimonials),
        'statistics' => count($statistics)
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطا در دریافت اطلاعات',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

