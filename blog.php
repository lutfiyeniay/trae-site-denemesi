<?php
// Blog Page - Cyberpunk 2077 Themed Streaming Site
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$posts_per_page = 9;
$offset = ($page - 1) * $posts_per_page;

// Build query conditions
$where_conditions = ['status = ?'];
$params = ['published'];

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

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM blog_posts $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_posts = $count_stmt->fetchColumn();
    
    // Get posts
    $sql = "SELECT id, title, slug, excerpt, content, category, featured_image, views, created_at, updated_at 
            FROM blog_posts 
            $where_clause 
            ORDER BY created_at DESC 
            LIMIT $posts_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $cat_sql = "SELECT category, COUNT(*) as count FROM blog_posts WHERE status = 'published' GROUP BY category ORDER BY category";
    $cat_stmt = $pdo->query($cat_sql);
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate pagination
    $total_pages = ceil($total_posts / $posts_per_page);
    
} catch (PDOException $e) {
    $posts = [];
    $categories = [];
    $total_posts = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Su Ascend</title>
    <meta name="description" content="Cyberpunk dünyasından oyun, film, dizi ve kitap incelemeleri. Gaming ve teknoloji blog yazıları.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Cyber Grid Background -->
    <div class="cyber-grid"></div>
    
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <h1 class="logo"><a href="index.html">SU<span class="accent">ASCEND</span></a></h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html#streams" class="nav-link">Yayınlar</a></li>
                <li><a href="blog.php" class="nav-link active">Blog</a></li>
                <li><a href="index.html" class="nav-link">Ana Sayfa</a></li>
                <li><a href="admin/login.php" class="nav-link admin-link">Admin</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Blog Header -->
    <section class="blog-header">
        <div class="container">
            <h1 class="page-title">CYBER <span class="accent">BLOG</span></h1>
            <p class="page-subtitle">
                DENEME
            </p>
            
            <!-- Search and Filter -->
            <div class="blog-controls">
                <form class="search-form" method="GET">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Blog yazılarında ara..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                </form>
                
                <div class="category-filter">
                    <a href="blog.php" class="filter-btn <?php echo empty($category) ? 'active' : ''; ?>">
                        <i class="fas fa-th"></i>
                        Tümü
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="blog.php?category=<?php echo urlencode($cat['category']); ?>" 
                           class="filter-btn <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                            <i class="<?php echo getCategoryIcon($cat['category']); ?>"></i>
                            <?php echo htmlspecialchars($cat['category']); ?>
                            <span class="count"><?php echo $cat['count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="blog-content">
        <div class="container">
            <?php if (!empty($search) || !empty($category)): ?>
                <div class="filter-info">
                    <p>
                        <?php if (!empty($search)): ?>
                            "<strong><?php echo htmlspecialchars($search); ?></strong>" için arama sonuçları
                        <?php endif; ?>
                        <?php if (!empty($category)): ?>
                            <strong><?php echo htmlspecialchars($category); ?></strong> kategorisindeki yazılar
                        <?php endif; ?>
                        - <span class="result-count"><?php echo $total_posts; ?> yazı bulundu</span>
                    </p>
                    <a href="blog.php" class="clear-filters">
                        <i class="fas fa-times"></i>
                        Filtreleri Temizle
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <div class="no-posts-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Yazı bulunamadı</h3>
                    <p>
                        <?php if (!empty($search) || !empty($category)): ?>
                            Arama kriterlerinize uygun blog yazısı bulunamadı. Farklı anahtar kelimeler deneyin.
                        <?php else: ?>
                            Henüz blog yazısı bulunmuyor. Yakında cyberpunk dünyasından harika içerikler paylaşacağız!
                        <?php endif; ?>
                    </p>
                    <a href="blog.php" class="cyber-btn primary">
                        <i class="fas fa-home"></i>
                        Tüm Yazıları Gör
                    </a>
                </div>
            <?php else: ?>
                <div class="blog-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="blog-card">
                            <div class="blog-card-image">
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="<?php echo getCategoryIcon($post['category']); ?>"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="blog-card-category" style="background-color: <?php echo getCategoryColor($post['category']); ?>">
                                    <i class="<?php echo getCategoryIcon($post['category']); ?>"></i>
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </div>
                            </div>
                            <div class="blog-card-content">
                                <h3 class="blog-card-title">
                                    <a href="post.php?id=<?php echo $post['id']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h3>
                                <p class="blog-card-excerpt">
                                    <?php echo htmlspecialchars($post['excerpt'] ?: truncateText(strip_tags($post['content']), 150)); ?>
                                </p>
                                <div class="blog-card-meta">
                                    <span class="blog-card-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo formatDate($post['created_at']); ?>
                                    </span>
                                    <span class="blog-card-views">
                                        <i class="fas fa-eye"></i>
                                        <?php echo number_format($post['views']); ?> görüntüleme
                                    </span>
                                </div>
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="blog-card-link">
                                    Devamını Oku <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-btn prev">
                                <i class="fas fa-chevron-left"></i>
                                Önceki
                            </a>
                        <?php endif; ?>
                        
                        <div class="pagination-numbers">
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1): ?>
                                <a href="?page=1<?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-number">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $total_pages; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-number"><?php echo $total_pages; ?></a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-btn next">
                                Sonraki
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SU<span class="accent">ASCEND</span></h3>
                    <p>DENEME</p>
                    <div class="social-links">
                        <a href="https://twitch.tv/YOUR_TWITCH_USERNAME" target="_blank">
                            <i class="fab fa-twitch"></i>
                        </a>
                        <a href="https://kick.com/YOUR_KICK_USERNAME" target="_blank">
                            <i class="fas fa-video"></i>
                        </a>
                        <a href="#" target="_blank">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" target="_blank">
                            <i class="fab fa-discord"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Bağlantılar</h4>
                    <ul>
                        <li><a href="index.html#streams">Canlı Yayınlar</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="index.html">Ana Sayfa</a></li>
                        <li><a href="admin/login.php">Admin Panel</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Kategoriler</h4>
                    <ul>
                        <li><a href="blog.php?category=Oyun">Oyun İncelemeleri</a></li>
                        <li><a href="blog.php?category=Film">Film İncelemeleri</a></li>
                        <li><a href="blog.php?category=Dizi">Dizi İncelemeleri</a></li>
                        <li><a href="blog.php?category=Kitap">Kitap İncelemeleri</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>İletişim</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-envelope"></i> contact@suascend.local</p>
                        <p><i class="fas fa-globe"></i> www.suascend.local</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; DENEME.</p>
                <p>DENEME.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/script.js"></script>
</body>
</html>