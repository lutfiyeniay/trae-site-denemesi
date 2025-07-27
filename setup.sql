-- CyberStream Database Setup Script
-- Cyberpunk 2077 Themed Streaming Site

-- Create database
CREATE DATABASE IF NOT EXISTS cyberstream_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cyberstream_db;

-- Create blog_posts table
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
    INDEX idx_created_at (created_at),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create stream_settings table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create site_settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'boolean', 'number') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_logs table for activity tracking
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT IGNORE INTO admin_users (username, email, password_hash, role) 
VALUES ('admin', 'admin@cyberstream.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Default password is 'admin123'

-- Insert default site settings
INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_title', 'CyberStream', 'text', 'Site başlığı'),
('site_description', 'Cyberpunk dünyasında gaming, streaming ve daha fazlası', 'textarea', 'Site açıklaması'),
('twitch_username', '', 'text', 'Twitch kullanıcı adı'),
('kick_username', '', 'text', 'Kick kullanıcı adı'),
('twitch_client_id', '', 'text', 'Twitch Client ID'),
('twitch_access_token', '', 'text', 'Twitch Access Token'),
('maintenance_mode', '0', 'boolean', 'Bakım modu'),
('posts_per_page', '6', 'number', 'Sayfa başına gösterilecek yazı sayısı'),
('site_keywords', 'cyberpunk, gaming, streaming, blog, twitch, kick', 'text', 'Site anahtar kelimeleri'),
('contact_email', 'contact@cyberstream.local', 'text', 'İletişim e-posta adresi');

-- Insert default stream settings
INSERT IGNORE INTO stream_settings (platform, username, is_active) VALUES
('twitch', 'YOUR_TWITCH_USERNAME', TRUE),
('kick', 'YOUR_KICK_USERNAME', TRUE);

-- Insert sample blog posts
INSERT IGNORE INTO blog_posts (title, slug, content, excerpt, category, status, views) VALUES
(
    'Cyberpunk 2077: Phantom Liberty İncelemesi',
    'cyberpunk-2077-phantom-liberty-incelemesi',
    '<p>CD Projekt RED\'in uzun zamandır beklenen genişleme paketi Phantom Liberty sonunda burada. Night City\'de yeni maceralar, karakter gelişimleri ve hikaye anlatımı ile oyuncuları büyülemeye devam ediyor.</p>
    
    <h2>Hikaye ve Karakter Gelişimi</h2>
    <p>Bu genişleme paketi, ana oyunun eksikliklerini giderirken aynı zamanda yeni özellikler ve içerikler sunuyor. Keanu Reeves\'in Johnny Silverhand karakteri ile birlikte yeni karakterler de hikayeye dahil oluyor.</p>
    
    <h2>Teknik İyileştirmeler</h2>
    <p>Grafik kalitesi ve optimizasyon açısından da önemli iyileştirmeler yapılmış. Özellikle ray tracing teknolojisi ile Night City daha da etkileyici görünüyor.</p>
    
    <blockquote>"Phantom Liberty, Cyberpunk 2077\'nin gerçek potansiyelini gösteren bir genişleme paketi."</blockquote>
    
    <h3>Sonuç</h3>
    <p>Phantom Liberty, cyberpunk severlerin mutlaka oynaması gereken bir deneyim sunuyor. CD Projekt RED\'in Night City\'ye olan bağlılığını gösteren başarılı bir çalışma.</p>',
    'CD Projekt RED\'in uzun zamandır beklenen genişleme paketi sonunda burada. Phantom Liberty ile Night City\'de yeni maceralar...',
    'Oyun',
    'published',
    156
),
(
    'Blade Runner 2049: Cyberpunk Sinemasının Zirvesi',
    'blade-runner-2049-cyberpunk-sinemasinin-zirvesi',
    '<p>Denis Villeneuve\'in yönettiği Blade Runner 2049, orijinal Blade Runner\'ın mirasını başarıyla sürdüren bir bilim kurgu başyapıtı. Film, cyberpunk türünün en önemli temalarını modern sinema diliyle harmanlıyor.</p>
    
    <h2>Görsel Şölen</h2>
    <p>Ryan Gosling\'in canlandırdığı K karakteri, Harrison Ford\'un Rick Deckard\'ı ile birlikte unutulmaz bir ikili oluşturuyor. Filmin görsel efektleri ve sinematografisi gerçekten nefes kesici.</p>
    
    <h2>Müzik ve Atmosfer</h2>
    <p>Hans Zimmer ve Benjamin Wallfisch\'in müzikleri de filmin atmosferine mükemmel uyum sağlıyor. Cyberpunk severlerin mutlaka izlemesi gereken bir yapıt.</p>
    
    <h3>Cyberpunk Teması</h3>
    <p>Film, teknoloji ve insanlık arasındaki ilişkiyi derinlemesine işlerken, gelecek distopyasını etkileyici bir şekilde sunuyor.</p>',
    'Denis Villeneuve\'in yönettiği bu bilim kurgu başyapıtı, orijinal Blade Runner\'ın mirasını nasıl sürdürüyor?',
    'Film',
    'published',
    89
),
(
    'Neuromancer: Cyberpunk Edebiyatının Babası',
    'neuromancer-cyberpunk-edebiyatinin-babasi',
    '<p>William Gibson\'ın 1984 tarihli romanı Neuromancer, cyberpunk türünün temellerini atan ve günümüze kadar etkisini sürdüren bir klasik. Roman, dijital dünya ve gerçeklik arasındaki sınırları sorgulayan öncü bir yapıt.</p>
    
    <h2>Siber Uzam Kavramı</h2>
    <p>Case karakterinin siber uzamdaki maceraları, teknolojinin insan yaşamına etkilerini derinlemesine işliyor. Gibson\'ın yarattığı terimler ve kavramlar, bugün hala kullanılıyor.</p>
    
    <h2>Günümüze Etkileri</h2>
    <p>Matrix, yapay zeka ve siber güvenlik gibi konular, romanın yazıldığı dönemde hayal ürünüyken, bugün günlük yaşamımızın bir parçası haline geldi.</p>
    
    <blockquote>"Cyberspace. A consensual hallucination experienced daily by billions of legitimate operators."</blockquote>
    
    <h3>Edebiyat Tarihindeki Yeri</h3>
    <p>Neuromancer, sadece cyberpunk türünü yaratmakla kalmadı, aynı zamanda bilim kurgu edebiyatına yeni bir soluk getirdi.</p>',
    'William Gibson\'ın 1984 tarihli romanı cyberpunk türünün temellerini nasıl attı ve günümüze nasıl etki ediyor?',
    'Kitap',
    'published',
    234
),
(
    'Altered Carbon: Netflix\'in Cyberpunk Dizisi',
    'altered-carbon-netflix-cyberpunk-dizisi',
    '<p>Richard K. Morgan\'ın romanından uyarlanan Altered Carbon, Netflix\'in cyberpunk türündeki en iddialı projelerinden biri. Dizi, ölümsüzlük teknolojisi ve kimlik kavramlarını sorguluyor.</p>
    
    <h2>Oyuncu Kadrosu ve Performans</h2>
    <p>Joel Kinnaman ve Anthony Mackie\'nin başrolde olduğu dizi, görsel efektleri ve prodüksiyon değerleri ile dikkat çekiyor. Cyberpunk estetiği mükemmel şekilde yansıtılmış.</p>
    
    <h2>Teknolojik Konseptler</h2>
    <p>Dizi, bilinç transferi ve dijital ölümsüzlük gibi konuları işlerken, gelecekteki toplumsal yapıları da eleştiriyor.</p>
    
    <h3>Sonuç</h3>
    <p>Her ne kadar ikinci sezondan sonra iptal edilse de, cyberpunk severlerin mutlaka izlemesi gereken bir yapım. Özellikle birinci sezon gerçekten başarılı.</p>',
    'Netflix\'in cyberpunk türündeki en iddialı projelerinden biri olan Altered Carbon hakkında detaylı inceleme.',
    'Dizi',
    'published',
    67
),
(
    'Ghost in the Shell: Anime Cyberpunk\'ın Klasiği',
    'ghost-in-the-shell-anime-cyberpunk-klasigi',
    '<p>Masamune Shirow\'un manga serisinden uyarlanan Ghost in the Shell, anime dünyasının en önemli cyberpunk yapıtlarından biri. Hem manga hem de anime versiyonları, türün klasikleri arasında yer alıyor.</p>
    
    <h2>Felsefi Derinlik</h2>
    <p>Yapıt, teknoloji ve ruh arasındaki ilişkiyi sorgularken, gelecekteki toplumsal yapıları da eleştiriyor. Major Kusanagi karakteri, cyberpunk türünün en ikonik karakterlerinden biri.</p>
    
    <h2>Görsel Estetik</h2>
    <p>Anime\'nin görsel tasarımı ve aksiyon sahneleri, cyberpunk estetiğinin en güzel örneklerini sunuyor.</p>',
    'Masamune Shirow\'un cyberpunk klasiği Ghost in the Shell\'in anime ve manga dünyasındaki etkisi.',
    'Dizi',
    'draft',
    0
);

-- Create indexes for better performance
CREATE INDEX idx_posts_category_status ON blog_posts(category, status);
CREATE INDEX idx_posts_status_created ON blog_posts(status, created_at);
CREATE INDEX idx_admin_logs_admin_date ON admin_logs(admin_id, created_at);

-- Create views for easier data access
CREATE OR REPLACE VIEW published_posts AS
SELECT * FROM blog_posts WHERE status = 'published';

CREATE OR REPLACE VIEW post_stats AS
SELECT 
    category,
    COUNT(*) as total_posts,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_posts,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_posts,
    SUM(views) as total_views,
    AVG(views) as avg_views
FROM blog_posts 
GROUP BY category;

-- Success message
SELECT 'CyberStream database setup completed successfully!' as message;
SELECT 'Default admin user: admin / admin123' as login_info;
SELECT 'Please change default credentials after first login!' as security_warning;