<?php
/**
 * API: Get provinces for map visualization
 * Returns provinces with their status (exists in database or not)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();
    
    // Get all provinces from database
    $stmt = $conn->query("SELECT id, name, name_en, slug FROM provinces WHERE is_active = 1");
    $dbProvinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a map of slugs for quick lookup
    $provinceMap = [];
    foreach ($dbProvinces as $province) {
        $provinceMap[$province['slug']] = true;
    }
    
    // All 31 provinces of Iran with their slugs
    $allProvinces = [
        ['name' => 'تهران', 'name_en' => 'Tehran', 'slug' => 'tehran'],
        ['name' => 'قم', 'name_en' => 'Qom', 'slug' => 'qom'],
        ['name' => 'مرکزی', 'name_en' => 'Markazi', 'slug' => 'markazi'],
        ['name' => 'قزوین', 'name_en' => 'Qazvin', 'slug' => 'qazvin'],
        ['name' => 'گیلان', 'name_en' => 'Gilan', 'slug' => 'gilan'],
        ['name' => 'اردبیل', 'name_en' => 'Ardabil', 'slug' => 'ardabil'],
        ['name' => 'زنجان', 'name_en' => 'Zanjan', 'slug' => 'zanjan'],
        ['name' => 'آذربایجان شرقی', 'name_en' => 'East Azerbaijan', 'slug' => 'east-azerbaijan'],
        ['name' => 'آذربایجان غربی', 'name_en' => 'West Azerbaijan', 'slug' => 'west-azerbaijan'],
        ['name' => 'کردستان', 'name_en' => 'Kurdistan', 'slug' => 'kurdistan'],
        ['name' => 'همدان', 'name_en' => 'Hamadan', 'slug' => 'hamadan'],
        ['name' => 'کرمانشاه', 'name_en' => 'Kermanshah', 'slug' => 'kermanshah'],
        ['name' => 'ایلام', 'name_en' => 'Ilam', 'slug' => 'ilam'],
        ['name' => 'لرستان', 'name_en' => 'Lorestan', 'slug' => 'lorestan'],
        ['name' => 'خوزستان', 'name_en' => 'Khuzestan', 'slug' => 'khuzestan'],
        ['name' => 'چهارمحال و بختیاری', 'name_en' => 'Chaharmahal and Bakhtiari', 'slug' => 'chaharmahal-bakhtiari'],
        ['name' => 'کهگیلویه و بویراحمد', 'name_en' => 'Kohgiluyeh and Boyer-Ahmad', 'slug' => 'kohgiluyeh-boyer-ahmad'],
        ['name' => 'بوشهر', 'name_en' => 'Bushehr', 'slug' => 'bushehr'],
        ['name' => 'فارس', 'name_en' => 'Fars', 'slug' => 'fars'],
        ['name' => 'هرمزگان', 'name_en' => 'Hormozgan', 'slug' => 'hormozgan'],
        ['name' => 'سیستان و بلوچستان', 'name_en' => 'Sistan and Baluchestan', 'slug' => 'sistan-baluchestan'],
        ['name' => 'کرمان', 'name_en' => 'Kerman', 'slug' => 'kerman'],
        ['name' => 'یزد', 'name_en' => 'Yazd', 'slug' => 'yazd'],
        ['name' => 'اصفهان', 'name_en' => 'Isfahan', 'slug' => 'isfahan'],
        ['name' => 'سمنان', 'name_en' => 'Semnan', 'slug' => 'semnan'],
        ['name' => 'مازندران', 'name_en' => 'Mazandaran', 'slug' => 'mazandaran'],
        ['name' => 'گلستان', 'name_en' => 'Golestan', 'slug' => 'golestan'],
        ['name' => 'خراسان شمالی', 'name_en' => 'North Khorasan', 'slug' => 'north-khorasan'],
        ['name' => 'خراسان رضوی', 'name_en' => 'Razavi Khorasan', 'slug' => 'razavi-khorasan'],
        ['name' => 'خراسان جنوبی', 'name_en' => 'South Khorasan', 'slug' => 'south-khorasan'],
        ['name' => 'البرز', 'name_en' => 'Alborz', 'slug' => 'alborz']
    ];
    
    // Mark which provinces exist in database
    $result = [];
    foreach ($allProvinces as $province) {
        $result[] = [
            'name' => $province['name'],
            'name_en' => $province['name_en'],
            'slug' => $province['slug'],
            'exists' => isset($provinceMap[$province['slug']])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'provinces' => $result
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
