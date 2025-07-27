<?php
// API Endpoint for Blog Posts - Cyberpunk 2077 Themed Streaming Site
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Get parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'published';

// Validate parameters
$limit = max(1, min(50, $limit)); // Between 1 and 50
$offset = max(0, $offset);

try {
    // Build query conditions
    $where_conditions = ['status = ?'];
    $params = [$status];
    
    if (!empty($category)) {
        $where_conditions[] = 'category = ?';
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $where_conditions[] = '(title LIKE ? OR content LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM blog_posts $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetchColumn();
    
    // Get posts
    $sql = "SELECT id, title, slug, excerpt, category, featured_image, views, created_at, updated_at 
            FROM blog_posts 
            $where_clause 
            ORDER BY created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format posts
    $formatted_posts = [];
    foreach ($posts as $post) {
        $formatted_posts[] = [
            'id' => (int)$post['id'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'excerpt' => $post['excerpt'] ?: truncateText(strip_tags($post['content'] ?? ''), 150),
            'category' => $post['category'],
            'featured_image' => $post['featured_image'],
            'views' => (int)$post['views'],
            'created_at' => $post['created_at'],
            'updated_at' => $post['updated_at'],
            'formatted_date' => formatDate($post['created_at']),
            'category_icon' => getCategoryIcon($post['category']),
            'category_color' => getCategoryColor($post['category']),
            'url' => 'post.php?id=' . $post['id']
        ];
    }
    
    // Get categories for filter
    $cat_sql = "SELECT category, COUNT(*) as count FROM blog_posts WHERE status = 'published' GROUP BY category ORDER BY category";
    $cat_stmt = $pdo->query($cat_sql);
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Response
    $response = [
        'success' => true,
        'data' => [
            'posts' => $formatted_posts,
            'pagination' => [
                'total' => (int)$total_count,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total_count,
                'total_pages' => ceil($total_count / $limit),
                'current_page' => floor($offset / $limit) + 1
            ],
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'search' => $search,
                'status' => $status
            ]
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => 'Veritabanı hatası oluştu',
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred',
        'message' => 'Sunucu hatası oluştu',
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}
?>