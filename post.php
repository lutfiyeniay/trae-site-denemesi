<?php
// Single Post Page - Cyberpunk 2077 Themed Streaming Site
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get post ID and title from URL parameters
$post_slug = isset($_GET['id']) ? $_GET['id'] : '';
$post_title = isset($_GET['title']) ? $_GET['title'] : '';

// If we have a slug but no title, try to find by slug
// If we have a title, use it to find the post
if (empty($post_slug) && empty($post_title)) {
    header('Location: blog.html');
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

// Find post by slug or create default content
$post = null;
if (isset($demo_posts[$post_slug])) {
    $post = $demo_posts[$post_slug];
} else {
    // Create a default post with the provided title
    $post = [
        'id' => 999,
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