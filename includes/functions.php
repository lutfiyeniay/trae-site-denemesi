<?php
// Helper Functions - Cyberpunk 2077 Themed Streaming Site

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate URL-friendly slug from title
 */
function generateSlug($title) {
    // Turkish character replacements
    $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
    $english = ['c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'i', 'o', 's', 'u'];
    
    $title = str_replace($turkish, $english, $title);
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9\s-]/', '', $title);
    $title = preg_replace('/[\s-]+/', '-', $title);
    $title = trim($title, '-');
    
    return $title;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Check if user has admin role
 */
function isAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php?error=access_denied');
        exit();
    }
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y') {
    $months = [
        'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
        'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
        'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
        'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
    ];
    
    $formatted = date($format, strtotime($date));
    
    foreach ($months as $en => $tr) {
        $formatted = str_replace($en, $tr, $formatted);
    }
    
    return $formatted;
}

/**
 * Get site setting value
 */
function getSiteSetting($key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? $result : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Update site setting
 */
function updateSiteSetting($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Upload file with validation
 */
function uploadFile($file, $uploadDir = 'uploads/', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Dosya seçilmedi'];
    }
    
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    if ($fileError !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Dosya yükleme hatası'];
    }
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'error' => 'Geçersiz dosya türü'];
    }
    
    if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
        return ['success' => false, 'error' => 'Dosya boyutu çok büyük (max 5MB)'];
    }
    
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
    } else {
        return ['success' => false, 'error' => 'Dosya yüklenemedi'];
    }
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $params = []) {
    if ($totalPages <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevUrl = $baseUrl . '?page=' . ($currentPage - 1);
        foreach ($params as $key => $value) {
            $prevUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        $html .= '<a href="' . $prevUrl . '" class="pagination-btn"><i class="fas fa-chevron-left"></i> Önceki</a>';
    }
    
    // Page numbers
    $html .= '<div class="pagination-numbers">';
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $pageUrl = $baseUrl . '?page=' . $i;
        foreach ($params as $key => $value) {
            $pageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        
        $activeClass = ($i === $currentPage) ? ' active' : '';
        $html .= '<a href="' . $pageUrl . '" class="pagination-number' . $activeClass . '">' . $i . '</a>';
    }
    $html .= '</div>';
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl . '?page=' . ($currentPage + 1);
        foreach ($params as $key => $value) {
            $nextUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        $html .= '<a href="' . $nextUrl . '" class="pagination-btn">Sonraki <i class="fas fa-chevron-right"></i></a>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Truncate text with word boundary
 */
function truncateText($text, $length = 150, $suffix = '...') {
    $text = strip_tags($text);
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $truncated = substr($text, 0, $length);
    $lastSpace = strrpos($truncated, ' ');
    
    if ($lastSpace !== false) {
        $truncated = substr($truncated, 0, $lastSpace);
    }
    
    return $truncated . $suffix;
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Log admin activity
 */
function logActivity($action, $details = '') {
    global $pdo;
    
    if (!isLoggedIn()) return false;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, details, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $details,
            getClientIP()
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send JSON response
 */
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Check if string contains cyberpunk-related keywords
 */
function isCyberpunkContent($text) {
    $keywords = [
        'cyberpunk', 'cyber', 'neon', 'matrix', 'neural', 'android', 'cyborg',
        'dystopia', 'futuristic', 'tech', 'ai', 'virtual', 'digital', 'hack'
    ];
    
    $text = strtolower($text);
    foreach ($keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get category icon
 */
function getCategoryIcon($category) {
    $icons = [
        'Oyun' => 'fas fa-gamepad',
        'Film' => 'fas fa-film',
        'Dizi' => 'fas fa-tv',
        'Kitap' => 'fas fa-book',
        'Teknoloji' => 'fas fa-microchip',
        'İnceleme' => 'fas fa-star'
    ];
    
    return $icons[$category] ?? 'fas fa-file-alt';
}

/**
 * Get category color
 */
function getCategoryColor($category) {
    $colors = [
        'Oyun' => '#00ffff',
        'Film' => '#ff0080',
        'Dizi' => '#8a2be2',
        'Kitap' => '#ffff00',
        'Teknoloji' => '#00ff41',
        'İnceleme' => '#ff6600'
    ];
    
    return $colors[$category] ?? '#00ffff';
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Clean HTML content
 */
function cleanHTML($html) {
    $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre>';
    return strip_tags($html, $allowedTags);
}

/**
 * Check if maintenance mode is enabled
 */
function isMaintenanceMode() {
    return getSiteSetting('maintenance_mode', '0') === '1';
}

/**
 * Show maintenance page if enabled
 */
function checkMaintenanceMode() {
    if (isMaintenanceMode() && !isLoggedIn()) {
        include 'maintenance.php';
        exit();
    }
}

?>