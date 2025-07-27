<?php
// Single Post Page - Cyberpunk 2077 Themed Streaming Site
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get post ID and optional title from URL parameters
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post_title = isset($_GET['title']) ? $_GET['title'] : '';

// Require an ID or title
if ($post_id <= 0 && empty($post_title)) {
    header('Location: blog.php');
    exit();
}

// Demo post data (since we're using static content from blog.html)
$demo_posts = [
    'cyberpunk-2077-phantom-liberty-incelemesi' => [
        'id' => 1,
        'title' => 'Cyberpunk 2077: Phantom Liberty İncelemesi',
        'content' => '
            <h2>Cyberpunk 2077: Phantom Liberty İncelemesi</h2>
            <p><strong>Yayın Tarihi:</strong> 15 Aralık 2024</p>
            <p><strong>Kategori:</strong> Oyun İncelemesi</p>
            
            <h3>Giriş</h3>
            <p>Night City\'ye geri dönüş zamanı! CD Projekt RED\'in uzun zamandır beklenen genişleme paketi Phantom Liberty, Cyberpunk 2077\'nin hikayesini derinleştiriyor ve oyunculara yepyeni bir macera sunuyor.</p>
            
            <h3>Hikaye ve Karakterler</h3>
            <p>Phantom Liberty, V\'nin hikayesini devam ettirirken, yeni karakterler ve kompleks siyasi entrikalar ekliyor. Keanu Reeves\'in Johnny Silverhand karakteri yine merkezi bir rol oynuyor.</p>
            
            <h3>Oynanış</h3>
            <p>Genişleme paketi, temel oyunun mekaniğini geliştirirken yeni siber implantlar ve yetenekler ekliyor. Dogtown bölgesi, keşfedilecek yeni alanlar ve yan görevler sunuyor.</p>
            
            <h3>Sonuç</h3>
            <p>Phantom Liberty, Cyberpunk 2077\'nin potansiyelini gerçekleştiren mükemmel bir genişleme paketi. Hem yeni hem de eski oyuncular için kesinlikle denemeye değer.</p>
            
            <p><strong>Puan:</strong> 9/10</p>
        ',
        'excerpt' => 'Night City\'ye geri dönüş zamanı! Phantom Liberty genişleme paketi ile Cyberpunk 2077\'nin yeni hikayesi...',
        'category' => 'Oyun',
        'created_at' => '2024-12-15 10:00:00',
        'views' => 1234,
        'featured_image' => null,
        'status' => 'published'
    ],
    'blade-runner-2049-cyberpunk-sinemanin-zirvesi' => [
        'id' => 2,
        'title' => 'Blade Runner 2049: Cyberpunk Sinemanın Zirvesi',
        'content' => '
            <h2>Blade Runner 2049: Cyberpunk Sinemanın Zirvesi</h2>
            <p><strong>Yayın Tarihi:</strong> 12 Aralık 2024</p>
            <p><strong>Kategori:</strong> Film İncelemesi</p>
            
            <h3>Giriş</h3>
            <p>Denis Villeneuve\'ün yönettiği Blade Runner 2049, orijinal filmin mirasını korurken modern sinema teknikleriyle cyberpunk türünün zirvesine çıkıyor.</p>
            
            <h3>Görsel Tasarım</h3>
            <p>Roger Deakins\'in sinematografisi, neon ışıklar ve karanlık atmosferle cyberpunk estetiğini mükemmel şekilde yansıtıyor. Her kare bir sanat eseri.</p>
            
            <h3>Hikaye ve Temalar</h3>
            <p>Film, insan doğası, yapay zeka ve kimlik sorularını derinlemesine işliyor. Ryan Gosling\'in K karakteri, izleyiciyi duygusal bir yolculuğa çıkarıyor.</p>
            
            <h3>Sonuç</h3>
            <p>Blade Runner 2049, hem orijinal filmin hayranları hem de yeni izleyiciler için mükemmel bir cyberpunk deneyimi sunuyor.</p>
            
            <p><strong>Puan:</strong> 10/10</p>
        ',
        'excerpt' => 'Denis Villeneuve\'ün yönettiği bu başyapıt, cyberpunk türünün sinematik potansiyelini sonuna kadar kullanıyor...',
        'category' => 'Film',
        'created_at' => '2024-12-12 14:30:00',
        'views' => 987,
        'featured_image' => null,
        'status' => 'published'
    ],
    'altered-carbon-bilinc-transferi-ve-gelecek' => [
        'id' => 3,
        'title' => 'Altered Carbon: Bilinç Transferi ve Gelecek',
        'content' => '
            <h2>Altered Carbon: Bilinç Transferi ve Gelecek</h2>
            <p><strong>Yayın Tarihi:</strong> 10 Aralık 2024</p>
            <p><strong>Kategori:</strong> Dizi İncelemesi</p>
            
            <h3>Giriş</h3>
            <p>Netflix\'in cyberpunk dizisi Altered Carbon, ölümsüzlük ve kimlik kavramlarını sorguluyor. Richard K. Morgan\'ın romanından uyarlanan dizi, gelecekte bilinç transferi teknolojisinin yaygınlaştığı bir dünyayı anlatıyor.</p>
            
            <h3>Hikaye ve Dünya</h3>
            <p>Dizi, insanların bilincini dijital olarak saklayıp farklı bedenlere aktarabildiği bir gelecekte geçiyor. Bu teknoloji, zenginlerin ölümsüz olmasını sağlarken, yoksullar için erişilemez kalıyor.</p>
            
            <h3>Karakterler</h3>
            <p>Takeshi Kovacs karakteri, farklı bedenler arasında geçiş yaparak kimlik ve benlik sorularını gündeme getiriyor. Joel Kinnaman ve Anthony Mackie\'nin performansları dikkat çekici.</p>
            
            <h3>Sonuç</h3>
            <p>Altered Carbon, cyberpunk türünün temel temalarını modern bir yaklaşımla ele alıyor. Görsel efektleri ve hikayesi ile izlemeye değer bir yapım.</p>
            
            <p><strong>Puan:</strong> 8/10</p>
        ',
        'excerpt' => 'Netflix\'in cyberpunk dizisi Altered Carbon, ölümsüzlük ve kimlik kavramlarını sorguluyor...',
        'category' => 'Dizi',
        'created_at' => '2024-12-10 16:45:00',
        'views' => 756,
        'featured_image' => null,
        'status' => 'published'
    ]
];

// Find post by ID or fall back to demo/default content
$post = null;

if ($post_id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ?');
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $post = null;
    }
}

if (!$post) {
    // Search demo posts by ID
    foreach ($demo_posts as $demo) {
        if ($demo['id'] == $post_id) {
            $post = $demo;
            break;
        }
    }
}

if (!$post) {
    // Create a default post with the provided title
    $post = [
        'id' => $post_id ?: 999,
        'title' => $post_title ?: 'Blog Yazısı',
        'content' => '
            <h2>' . htmlspecialchars($post_title ?: 'Blog Yazısı') . '</h2>
            <p><strong>Yayın Tarihi:</strong> ' . date('d M Y') . '</p>
            <p><strong>Kategori:</strong> Genel</p>

            <h3>İçerik Yakında Eklenecek</h3>
            <p>Bu yazının detaylı içeriği yakında eklenecek. Cyberpunk dünyasından en güncel haberler ve incelemeleri takip etmeyi unutmayın!</p>

            <p>Daha fazla içerik için blog sayfamızı ziyaret edin.</p>
        ',
        'excerpt' => 'Bu yazının detaylı içeriği yakında eklenecek...',
        'category' => 'Genel',
        'created_at' => date('Y-m-d H:i:s'),
        'views' => 0,
        'featured_image' => null,
        'status' => 'published'
    ];
}

// Update view count (for demo purposes, we'll just increment)
$post['views']++;

// Get related posts (for demo, we'll show other demo posts)
$related_posts = array_slice(array_values($demo_posts), 0, 3);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Su Ascend</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="cyber-grid"></div>

    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <h1 class="logo"><a href="index.html">SU<span class="accent">ASCEND</span></a></h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html#streams" class="nav-link">Yayınlar</a></li>
                <li><a href="blog.php" class="nav-link">Blog</a></li>
                <li><a href="index.html" class="nav-link">Ana Sayfa</a></li>
            </ul>
        </nav>
    </header>

    <section class="post-detail">
        <div class="container">
            <?php if ($post): ?>
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="post-meta">
                    <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                    <span class="post-date"><?php echo formatDate($post['created_at']); ?></span>
                    <span class="post-views"><?php echo number_format($post['views']); ?> görüntüleme</span>
                </div>
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>
            <?php else: ?>
                <div class="no-post">
                    <h2>Yazı Bulunamadı</h2>
                    <p>Aradığınız blog yazısı bulunamadı.</p>
                    <a href="blog.php" class="cyber-btn primary">Blog'a Dön</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SU<span class="accent">ASCEND</span></h3>
                    <p>DENEME</p>
                    <div class="social-links">
                        <a href="https://twitch.tv/YOUR_TWITCH_USERNAME" target="_blank"><i class="fab fa-twitch"></i></a>
                        <a href="https://kick.com/YOUR_KICK_USERNAME" target="_blank"><i class="fas fa-video"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-youtube"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-discord"></i></a>
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

    <script src="js/script.js"></script>
</body>
</html>