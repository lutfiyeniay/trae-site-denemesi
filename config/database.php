<?php
// Database Configuration - Cyberpunk 2077 Themed Streaming Site

// Database credentials
$host = 'localhost';
$dbname = 'cyberstream_db';
$username = 'root';
$password = '';

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+03:00'");
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log('Database connection failed: ' . $e->getMessage());
    
    // In production, you might want to show a generic error message
    if (isset($_SERVER['HTTP_HOST'])) {
        die('<div style="background: #1a1a1a; color: #ff0080; padding: 20px; font-family: monospace; border: 2px solid #ff0080; margin: 20px; border-radius: 5px;">
                <h3>🔴 SYSTEM ERROR</h3>
                <p>Database connection failed. Please check your configuration.</p>
                <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><em>Please ensure MySQL is running and database credentials are correct.</em></p>
             </div>');
    } else {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Database setup function
function setupDatabase($pdo) {
    try {
        // Create blog_posts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS blog_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                content TEXT NOT NULL,
                excerpt TEXT,
                category VARCHAR(100) NOT NULL,
                featured_image VARCHAR(255),
                status ENUM('draft', 'published') DEFAULT 'draft',
                views INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create admin_users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'editor') DEFAULT 'editor',
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create stream_settings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS stream_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                platform VARCHAR(50) NOT NULL,
                username VARCHAR(100) NOT NULL,
                api_key VARCHAR(255),
                client_id VARCHAR(255),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_platform (platform)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create site_settings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('text', 'textarea', 'boolean', 'number') DEFAULT 'text',
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Insert default admin user if not exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $default_password = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->prepare("
                INSERT INTO admin_users (username, email, password_hash, role) 
                VALUES ('admin', 'admin@cyberstream.local', ?, 'admin')
            ")->execute([$default_password]);
        }
        
        // Insert default site settings
        $default_settings = [
            ['site_title', 'CyberStream', 'text', 'Site başlığı'],
            ['site_description', 'Cyberpunk dünyasında gaming, streaming ve daha fazlası', 'textarea', 'Site açıklaması'],
            ['twitch_username', '', 'text', 'Twitch kullanıcı adı'],
            ['kick_username', '', 'text', 'Kick kullanıcı adı'],
            ['twitch_client_id', '', 'text', 'Twitch Client ID'],
            ['twitch_access_token', '', 'text', 'Twitch Access Token'],
            ['maintenance_mode', '0', 'boolean', 'Bakım modu'],
            ['posts_per_page', '6', 'number', 'Sayfa başına gösterilecek yazı sayısı']
        ];
        
        foreach ($default_settings as $setting) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$setting[0]]);
            
            if ($stmt->fetchColumn() == 0) {
                $pdo->prepare("
                    INSERT INTO site_settings (setting_key, setting_value, setting_type, description) 
                    VALUES (?, ?, ?, ?)
                ")->execute($setting);
            }
        }
        
        // Insert sample blog posts if table is empty
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $sample_posts = [
                [
                    'title' => 'Cyberpunk 2077: Phantom Liberty İncelemesi',
                    'slug' => 'cyberpunk-2077-phantom-liberty-incelemesi',
                    'content' => '<p>CD Projekt RED\'in uzun zamandır beklenen genişleme paketi Phantom Liberty sonunda burada. Night City\'de yeni maceralar, karakter gelişimleri ve hikaye anlatımı ile oyuncuları büyülemeye devam ediyor.</p><p>Bu genişleme paketi, ana oyunun eksikliklerini giderirken aynı zamanda yeni özellikler ve içerikler sunuyor. Keanu Reeves\'in Johnny Silverhand karakteri ile birlikte yeni karakterler de hikayeye dahil oluyor.</p><p>Grafik kalitesi ve optimizasyon açısından da önemli iyileştirmeler yapılmış. Özellikle ray tracing teknolojisi ile Night City daha da etkileyici görünüyor.</p>',
                    'excerpt' => 'CD Projekt RED\'in uzun zamandır beklenen genişleme paketi sonunda burada. Phantom Liberty ile Night City\'de yeni maceralar...',
                    'category' => 'Oyun',
                    'status' => 'published'
                ],
                [
                    'title' => 'Blade Runner 2049: Cyberpunk Sinemasının Zirvesi',
                    'slug' => 'blade-runner-2049-cyberpunk-sinemasinin-zirvesi',
                    'content' => '<p>Denis Villeneuve\'in yönettiği Blade Runner 2049, orijinal Blade Runner\'ın mirasını başarıyla sürdüren bir bilim kurgu başyapıtı. Film, cyberpunk türünün en önemli temalarını modern sinema diliyle harmanlıyor.</p><p>Ryan Gosling\'in canlandırdığı K karakteri, Harrison Ford\'un Rick Deckard\'ı ile birlikte unutulmaz bir ikili oluşturuyor. Filmin görsel efektleri ve sinematografisi gerçekten nefes kesici.</p><p>Hans Zimmer ve Benjamin Wallfisch\'in müzikleri de filmin atmosferine mükemmel uyum sağlıyor. Cyberpunk severlerin mutlaka izlemesi gereken bir yapıt.</p>',
                    'excerpt' => 'Denis Villeneuve\'in yönettiği bu bilim kurgu başyapıtı, orijinal Blade Runner\'ın mirasını nasıl sürdürüyor?',
                    'category' => 'Film',
                    'status' => 'published'
                ],
                [
                    'title' => 'Neuromancer: Cyberpunk Edebiyatının Babası',
                    'slug' => 'neuromancer-cyberpunk-edebiyatinin-babasi',
                    'content' => '<p>William Gibson\'ın 1984 tarihli romanı Neuromancer, cyberpunk türünün temellerini atan ve günümüze kadar etkisini sürdüren bir klasik. Roman, dijital dünya ve gerçeklik arasındaki sınırları sorgulayan öncü bir yapıt.</p><p>Case karakterinin siber uzamdaki maceraları, teknolojinin insan yaşamına etkilerini derinlemesine işliyor. Gibson\'ın yarattığı terimler ve kavramlar, bugün hala kullanılıyor.</p><p>Matrix, yapay zeka ve siber güvenlik gibi konular, romanın yazıldığı dönemde hayal ürünüyken, bugün günlük yaşamımızın bir parçası haline geldi.</p>',
                    'excerpt' => 'William Gibson\'ın 1984 tarihli romanı cyberpunk türünün temellerini nasıl attı ve günümüze nasıl etki ediyor?',
                    'category' => 'Kitap',
                    'status' => 'published'
                ],
                [
                    'title' => 'Altered Carbon: Netflix\'in Cyberpunk Dizisi',
                    'slug' => 'altered-carbon-netflix-cyberpunk-dizisi',
                    'content' => '<p>Richard K. Morgan\'ın romanından uyarlanan Altered Carbon, Netflix\'in cyberpunk türündeki en iddialı projelerinden biri. Dizi, ölümsüzlük teknolojisi ve kimlik kavramlarını sorguluyor.</p><p>Joel Kinnaman ve Anthony Mackie\'nin başrolde olduğu dizi, görsel efektleri ve prodüksiyon değerleri ile dikkat çekiyor. Cyberpunk estetiği mükemmel şekilde yansıtılmış.</p><p>Her ne kadar ikinci sezondan sonra iptal edilse de, cyberpunk severlerin mutlaka izlemesi gereken bir yapım. Özellikle birinci sezon gerçekten başarılı.</p>',
                    'excerpt' => 'Netflix\'in cyberpunk türündeki en iddialı projelerinden biri olan Altered Carbon hakkında detaylı inceleme.',
                    'category' => 'Dizi',
                    'status' => 'published'
                ]
            ];
            
            foreach ($sample_posts as $post) {
                $pdo->prepare("
                    INSERT INTO blog_posts (title, slug, content, excerpt, category, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ")->execute([
                    $post['title'],
                    $post['slug'],
                    $post['content'],
                    $post['excerpt'],
                    $post['category'],
                    $post['status']
                ]);
            }
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log('Database setup failed: ' . $e->getMessage());
        return false;
    }
}

// Auto-setup database if tables don't exist
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'blog_posts'");
        if ($stmt->rowCount() == 0) {
            setupDatabase($pdo);
        }
    } catch (PDOException $e) {
        // Ignore errors during auto-setup check
    }
}

?>