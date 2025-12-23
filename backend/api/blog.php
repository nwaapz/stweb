<?php
/**
 * Blog Posts API
 * API پست‌های وبلاگ
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

$conn = getConnection();
$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Get single post by ID
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $post = getBlogPostById($id);
        
        if ($post) {
            // Only return published posts for public API (unless admin)
            if (!$post['is_published'] && !isLoggedIn()) {
                $response['message'] = 'پست یافت نشد';
            } else {
                // Increment views
                $stmt = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
                $stmt->execute([$id]);
                
                $response['success'] = true;
                $response['data'] = $post;
            }
        } else {
            $response['message'] = 'پست یافت نشد';
        }
    }
    // Get single post by slug
    elseif (isset($_GET['slug'])) {
        $slug = sanitize($_GET['slug']);
        $post = getBlogPostBySlug($slug);
        
        if ($post) {
            // Increment views
            $stmt = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
            $stmt->execute([$post['id']]);
            
            $response['success'] = true;
            $response['data'] = $post;
        } else {
            $response['message'] = 'پست یافت نشد';
        }
    }
    // Get list of posts
    else {
        $filters = [];
        
        // Only show published posts for public API
        if (!isLoggedIn()) {
            $filters['is_published'] = 1;
        } elseif (isset($_GET['published'])) {
            $filters['is_published'] = (int)$_GET['published'];
        }
        
        // Search
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        // Author filter
        if (isset($_GET['author_id'])) {
            $filters['author_id'] = (int)$_GET['author_id'];
        }
        
        // Pagination
        if (isset($_GET['limit'])) {
            $filters['limit'] = (int)$_GET['limit'];
        }
        
        if (isset($_GET['offset'])) {
            $filters['offset'] = (int)$_GET['offset'];
        }
        
        // Order by
        if (isset($_GET['order_by'])) {
            $filters['order_by'] = sanitize($_GET['order_by']);
        }
        
        if (isset($_GET['order_dir'])) {
            $filters['order_dir'] = strtoupper($_GET['order_dir']) === 'ASC' ? 'ASC' : 'DESC';
        }
        
        $posts = getBlogPosts($filters);
        
        // Format posts for API response
        $formattedPosts = [];
        foreach ($posts as $post) {
            $formattedPosts[] = [
                'id' => (int)$post['id'],
                'title' => $post['title'],
                'slug' => $post['slug'],
                'excerpt' => $post['excerpt'],
                'content' => $post['content'],
                'featured_image' => $post['featured_image'] ? UPLOAD_URL . $post['featured_image'] : null,
                'author_name' => $post['author_name'] ?? 'نامشخص',
                'author_id' => $post['author_id'] ? (int)$post['author_id'] : null,
                'is_published' => (bool)$post['is_published'],
                'published_at' => $post['published_at'],
                'views' => (int)$post['views'],
                'meta_title' => $post['meta_title'],
                'meta_description' => $post['meta_description'],
                'created_at' => $post['created_at'],
                'updated_at' => $post['updated_at']
            ];
        }
        
        $response['success'] = true;
        $response['data'] = $formattedPosts;
        $response['count'] = count($formattedPosts);
    }
    
} catch (Exception $e) {
    $response['message'] = 'خطا در دریافت اطلاعات: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

