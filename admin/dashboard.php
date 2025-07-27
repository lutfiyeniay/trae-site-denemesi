<?php
// Admin Dashboard - Cyberpunk 2077 Themed Streaming Site
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get dashboard statistics
try {
    // Get total posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $total_posts = $stmt->fetchColumn();
    
    // Get published posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
    $published_posts = $stmt->fetchColumn();
    
    // Get draft posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'");
    $draft_posts = $stmt->fetchColumn();
    
    // Get total views
    $stmt = $pdo->query("SELECT SUM(views) FROM blog_posts");
    $total_views = $stmt->fetchColumn() ?: 0;
    
    // Get recent posts
    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 5");
    $recent_posts = $stmt->fetchAll();
    
    // Get categories with post counts
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM blog_posts GROUP BY category ORDER BY count DESC");
    $categories = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $total_posts = $published_posts = $draft_posts = $total_views = 0;
    $recent_posts = $categories = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Su Ascend</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <div class="cyber-grid"></div>
    
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <i class="fas fa-shield-alt"></i>
                <h1>CYBER<span class="accent">ADMIN</span></h1>
            </div>
            <nav class="admin-menu">
                <a href="dashboard.php" class="admin-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="posts.php" class="admin-link">
                    <i class="fas fa-edit"></i>
                    Blog Yazıları
                </a>
                <a href="settings.php" class="admin-link">
                    <i class="fas fa-cog"></i>
                    Ayarlar
                </a>
                <a href="../index.html" class="admin-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    Siteyi Görüntüle
                </a>
            </nav>
            <div class="admin-user">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['admin_role']); ?></span>
                </div>
                <div class="user-actions">
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Çıkış
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="admin-main">
        <div class="admin-container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2 class="page-title">Hoş Geldiniz, <span class="accent"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span></h2>
                <p class="page-subtitle">Su Ascend yönetim paneline hoş geldiniz. Buradan sitenizi yönetebilirsiniz.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($total_posts); ?></h3>
                        <p class="stat-label">Toplam Yazı</p>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon published">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($published_posts); ?></h3>
                        <p class="stat-label">Yayınlanan</p>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon draft">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($draft_posts); ?></h3>
                        <p class="stat-label">Taslak</p>
                    </div>
                    <div class="stat-trend neutral">
                        <i class="fas fa-minus"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon views">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo number_format($total_views); ?></h3>
                        <p class="stat-label">Toplam Görüntüleme</p>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Posts -->
                <div class="content-panel">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <i class="fas fa-clock"></i>
                            Son Yazılar
                        </h3>
                        <a href="posts.php" class="panel-action">
                            Tümünü Gör <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="panel-content">
                        <?php if (empty($recent_posts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>Henüz blog yazısı bulunmuyor</p>
                                <a href="post-edit.php" class="cyber-btn-sm">İlk Yazıyı Oluştur</a>
                            </div>
                        <?php else: ?>
                            <div class="posts-list">
                                <?php foreach ($recent_posts as $post): ?>
                                    <div class="post-item">
                                        <div class="post-info">
                                            <h4 class="post-title">
                                                <a href="post-edit.php?id=<?php echo $post['id']; ?>">
                                                    <?php echo htmlspecialchars($post['title']); ?>
                                                </a>
                                            </h4>
                                            <div class="post-meta">
                                                <span class="post-category" data-category="<?php echo htmlspecialchars($post['category']); ?>">
                                                    <?php echo htmlspecialchars($post['category']); ?>
                                                </span>
                                                <span class="post-date">
                                                    <?php echo formatDate($post['created_at']); ?>
                                                </span>
                                                <span class="post-status <?php echo $post['status']; ?>">
                                                    <?php echo $post['status'] === 'published' ? 'Yayınlandı' : 'Taslak'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="post-actions">
                                            <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="action-btn edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../post.php?id=<?php echo $post['id']; ?>" class="action-btn view" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Categories -->
                <div class="content-panel">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <i class="fas fa-tags"></i>
                            Kategoriler
                        </h3>
                    </div>
                    <div class="panel-content">
                        <?php if (empty($categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <p>Henüz kategori bulunmuyor</p>
                            </div>
                        <?php else: ?>
                            <div class="categories-list">
                                <?php foreach ($categories as $category): ?>
                                    <div class="category-item">
                                        <div class="category-info">
                                            <span class="category-name" data-category="<?php echo htmlspecialchars($category['category']); ?>">
                                                <i class="<?php echo getCategoryIcon($category['category']); ?>"></i>
                                                <?php echo htmlspecialchars($category['category']); ?>
                                            </span>
                                        </div>
                                        <div class="category-count">
                                            <?php echo $category['count']; ?> yazı
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-panel">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <i class="fas fa-bolt"></i>
                            Hızlı İşlemler
                        </h3>
                    </div>
                    <div class="panel-content">
                        <div class="quick-actions">
                            <a href="post-edit.php" class="quick-action">
                                <i class="fas fa-plus"></i>
                                <span>Yeni Yazı</span>
                            </a>
                            <a href="posts.php" class="quick-action">
                                <i class="fas fa-list"></i>
                                <span>Yazıları Yönet</span>
                            </a>
                            <a href="settings.php" class="quick-action">
                                <i class="fas fa-cog"></i>
                                <span>Site Ayarları</span>
                            </a>
                            <a href="../blog.php" class="quick-action" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                                <span>Blogu Görüntüle</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="content-panel">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <i class="fas fa-info-circle"></i>
                            Sistem Bilgileri
                        </h3>
                    </div>
                    <div class="panel-content">
                        <div class="system-info">
                            <div class="info-item">
                                <span class="info-label">PHP Sürümü:</span>
                                <span class="info-value"><?php echo PHP_VERSION; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sunucu:</span>
                                <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Veritabanı:</span>
                                <span class="info-value">MySQL <?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Son Giriş:</span>
                                <span class="info-value"><?php echo date('d.m.Y H:i'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
    /* Admin Dashboard Styles */
    .admin-body {
        background: var(--primary-bg);
        min-height: 100vh;
        padding-top: 80px;
    }
    
    .admin-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: rgba(10, 10, 10, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 2px solid var(--neon-cyan);
        z-index: 1000;
        box-shadow: var(--shadow-glow);
    }
    
    .admin-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .admin-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .admin-brand i {
        font-size: 1.8rem;
        color: var(--neon-cyan);
        text-shadow: 0 0 15px var(--neon-cyan);
    }
    
    .admin-brand h1 {
        font-family: 'Orbitron', monospace;
        font-size: 1.5rem;
        font-weight: 900;
        color: var(--text-primary);
        text-shadow: 0 0 10px var(--neon-cyan);
        margin: 0;
    }
    
    .admin-menu {
        display: flex;
        gap: 2rem;
    }
    
    .admin-link {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 5px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }
    
    .admin-link:hover,
    .admin-link.active {
        color: var(--neon-cyan);
        background: rgba(0, 255, 255, 0.1);
        text-shadow: 0 0 10px var(--neon-cyan);
    }
    
    .admin-user {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .user-info {
        text-align: right;
    }
    
    .user-name {
        display: block;
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .user-role {
        display: block;
        color: var(--text-muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .logout-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--neon-pink);
        text-decoration: none;
        font-weight: 500;
        padding: 8px 12px;
        border: 1px solid var(--neon-pink);
        border-radius: 5px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .logout-btn:hover {
        background: var(--neon-pink);
        color: var(--primary-bg);
        box-shadow: 0 0 15px var(--neon-pink);
    }
    
    .admin-main {
        padding: 2rem 0;
    }
    
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    .welcome-section {
        margin-bottom: 3rem;
        text-align: center;
    }
    
    .page-title {
        font-family: 'Orbitron', monospace;
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .page-subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        font-weight: 300;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    
    .stat-card {
        background: var(--accent-bg);
        border: 2px solid var(--border-glow);
        border-radius: 10px;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--neon-cyan);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--primary-bg);
        box-shadow: 0 0 20px var(--neon-cyan);
    }
    
    .stat-icon.published {
        background: var(--neon-green);
        box-shadow: 0 0 20px var(--neon-green);
    }
    
    .stat-icon.draft {
        background: var(--neon-yellow);
        box-shadow: 0 0 20px var(--neon-yellow);
    }
    
    .stat-icon.views {
        background: var(--neon-pink);
        box-shadow: 0 0 20px var(--neon-pink);
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-family: 'Orbitron', monospace;
    }
    
    .stat-label {
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }
    
    .stat-trend {
        font-size: 1.2rem;
    }
    
    .stat-trend.positive {
        color: var(--neon-green);
    }
    
    .stat-trend.neutral {
        color: var(--text-muted);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
    }
    
    .content-panel {
        background: var(--accent-bg);
        border: 2px solid var(--border-glow);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: var(--shadow-glow);
    }
    
    .panel-header {
        background: rgba(0, 0, 0, 0.3);
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-glow);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-primary);
        font-weight: 600;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }
    
    .panel-action {
        color: var(--neon-cyan);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    
    .panel-action:hover {
        color: var(--neon-pink);
        text-shadow: 0 0 10px var(--neon-pink);
    }
    
    .panel-content {
        padding: 1.5rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--text-muted);
    }
    
    .cyber-btn-sm {
        display: inline-block;
        background: var(--neon-cyan);
        color: var(--primary-bg);
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }
    
    .cyber-btn-sm:hover {
        background: var(--neon-pink);
        box-shadow: 0 0 15px var(--neon-pink);
    }
    
    .posts-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .post-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: var(--secondary-bg);
        border-radius: 5px;
        border: 1px solid var(--border-glow);
        transition: all 0.3s ease;
    }
    
    .post-item:hover {
        border-color: var(--neon-cyan);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
    }
    
    .post-title {
        margin-bottom: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .post-title a {
        color: var(--text-primary);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .post-title a:hover {
        color: var(--neon-cyan);
    }
    
    .post-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        font-size: 0.8rem;
    }
    
    .post-category {
        background: var(--neon-cyan);
        color: var(--primary-bg);
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .post-date {
        color: var(--text-muted);
    }
    
    .post-status {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .post-status.published {
        color: var(--neon-green);
    }
    
    .post-status.draft {
        color: var(--neon-yellow);
    }
    
    .post-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .action-btn.edit {
        color: var(--neon-cyan);
        border-color: var(--neon-cyan);
    }
    
    .action-btn.edit:hover {
        background: var(--neon-cyan);
        color: var(--primary-bg);
    }
    
    .action-btn.view {
        color: var(--neon-green);
        border-color: var(--neon-green);
    }
    
    .action-btn.view:hover {
        background: var(--neon-green);
        color: var(--primary-bg);
    }
    
    .categories-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .category-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: var(--secondary-bg);
        border-radius: 5px;
        border: 1px solid var(--border-glow);
    }
    
    .category-name {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-primary);
        font-weight: 600;
    }
    
    .category-count {
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 1.5rem;
        background: var(--secondary-bg);
        border: 2px solid var(--border-glow);
        border-radius: 10px;
        text-decoration: none;
        color: var(--text-secondary);
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .quick-action:hover {
        color: var(--neon-cyan);
        border-color: var(--neon-cyan);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    
    .quick-action i {
        font-size: 2rem;
    }
    
    .quick-action span {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .system-info {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem;
        background: var(--secondary-bg);
        border-radius: 5px;
        border: 1px solid var(--border-glow);
    }
    
    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
    }
    
    .info-value {
        color: var(--text-primary);
        font-weight: 600;
        font-family: 'Orbitron', monospace;
    }
    
    @media (max-width: 768px) {
        .admin-nav {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }
        
        .admin-menu {
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .admin-body {
            padding-top: 120px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions {
            grid-template-columns: 1fr;
        }
        
        .post-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .post-actions {
            align-self: flex-end;
        }
    }
    </style>

    <script>
    // Dashboard JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Add some interactive effects
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Auto-refresh stats every 5 minutes
        setInterval(function() {
            // You can add AJAX calls here to refresh statistics
            console.log('Stats refresh interval');
        }, 300000);
    });
    </script>
</body>
</html>