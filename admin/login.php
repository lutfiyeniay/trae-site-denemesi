<?php
// Admin Login Page - Cyberpunk 2077 Themed Streaming Site
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role FROM admin_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                    // Store token in database (you might want to create a remember_tokens table)
                }
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Geçersiz kullanıcı adı veya şifre';
            }
        } catch (PDOException $e) {
            $error = 'Giriş işlemi sırasında bir hata oluştu';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - Su Ascend</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <div class="cyber-grid"></div>
    
    <div class="admin-login-container">
        <div class="login-panel">
            <div class="login-header">
                <div class="admin-logo">
                    <i class="fas fa-shield-alt"></i>
                    <h1>CYBER<span class="accent">ADMIN</span></h1>
                </div>
                <p class="login-subtitle">Sistem Yönetici Paneli</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>
                        Kullanıcı Adı / E-posta
                    </label>
                    <input type="text" id="username" name="username" class="form-input" 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                           placeholder="Kullanıcı adınızı girin" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Şifre
                    </label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Şifrenizi girin" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" class="checkbox-input">
                        <span class="checkbox-custom"></span>
                        Beni hatırla
                    </label>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Giriş Yap
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Ana Sayfaya Dön
                </a>
            </div>
        </div>
        
        <div class="login-info">
            <div class="info-panel">
                <h3>Sistem Bilgileri</h3>
                <div class="system-stats">
                    <div class="stat-item">
                        <i class="fas fa-server"></i>
                        <span>Sistem Durumu: <span class="status-online">Çevrimiçi</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-shield-check"></i>
                        <span>Güvenlik: <span class="status-secure">Güvenli</span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <span>Son Güncelleme: <?php echo date('d.m.Y H:i'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="demo-credentials">
                <h4>Demo Giriş Bilgileri</h4>
                <p><strong>Kullanıcı Adı:</strong> admin</p>
                <p><strong>Şifre:</strong> admin123</p>
                <small>Bu bilgiler sadece demo amaçlıdır. Gerçek kullanımda değiştirin.</small>
            </div>
        </div>
    </div>

    <style>
    .admin-body {
        background: var(--primary-bg);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .admin-login-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        max-width: 1000px;
        width: 100%;
        align-items: start;
    }
    
    .login-panel {
        background: var(--accent-bg);
        border: 2px solid var(--border-glow);
        border-radius: 10px;
        padding: 2.5rem;
        box-shadow: var(--shadow-glow);
        position: relative;
        overflow: hidden;
    }
    
    .login-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--neon-cyan), var(--neon-pink), var(--neon-yellow));
        animation: gradientShift 3s ease-in-out infinite;
    }
    
    @keyframes gradientShift {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .admin-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 1rem;
    }
    
    .admin-logo i {
        font-size: 2.5rem;
        color: var(--neon-cyan);
        text-shadow: 0 0 15px var(--neon-cyan);
    }
    
    .admin-logo h1 {
        font-family: 'Orbitron', monospace;
        font-size: 2rem;
        font-weight: 900;
        color: var(--text-primary);
        text-shadow: 0 0 10px var(--neon-cyan);
        margin: 0;
    }
    
    .login-subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        font-weight: 300;
        margin: 0;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }
    
    .alert-error {
        background: rgba(255, 0, 128, 0.1);
        border: 1px solid var(--neon-pink);
        color: var(--neon-pink);
    }
    
    .alert-success {
        background: rgba(0, 255, 65, 0.1);
        border: 1px solid var(--neon-green);
        color: var(--neon-green);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 16px;
        background: var(--secondary-bg);
        border: 2px solid var(--border-glow);
        border-radius: 5px;
        color: var(--text-primary);
        font-size: 1rem;
        transition: all 0.3s ease;
        outline: none;
    }
    
    .form-input:focus {
        border-color: var(--neon-cyan);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
    }
    
    .form-input::placeholder {
        color: var(--text-muted);
    }
    
    .password-input-group {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }
    
    .password-toggle:hover {
        color: var(--neon-cyan);
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: var(--text-secondary);
        font-weight: 400;
        text-transform: none;
        font-size: 1rem;
        letter-spacing: normal;
    }
    
    .checkbox-input {
        display: none;
    }
    
    .checkbox-custom {
        width: 18px;
        height: 18px;
        border: 2px solid var(--border-glow);
        border-radius: 3px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .checkbox-input:checked + .checkbox-custom {
        background: var(--neon-cyan);
        border-color: var(--neon-cyan);
    }
    
    .checkbox-input:checked + .checkbox-custom::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: var(--primary-bg);
        font-weight: bold;
        font-size: 12px;
    }
    
    .login-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--neon-cyan), var(--neon-pink));
        border: none;
        border-radius: 5px;
        color: var(--primary-bg);
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 25px rgba(0, 255, 255, 0.5);
    }
    
    .login-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-glow);
    }
    
    .back-link {
        color: var(--text-secondary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease;
        font-weight: 500;
    }
    
    .back-link:hover {
        color: var(--neon-cyan);
        text-shadow: 0 0 10px var(--neon-cyan);
    }
    
    .login-info {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    .info-panel,
    .demo-credentials {
        background: var(--accent-bg);
        border: 2px solid var(--border-glow);
        border-radius: 10px;
        padding: 2rem;
        box-shadow: var(--shadow-glow);
    }
    
    .info-panel h3,
    .demo-credentials h4 {
        color: var(--text-primary);
        font-family: 'Orbitron', monospace;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .system-stats {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-secondary);
        font-weight: 500;
    }
    
    .stat-item i {
        color: var(--neon-cyan);
        width: 20px;
        text-align: center;
    }
    
    .status-online {
        color: var(--neon-green);
        font-weight: 600;
    }
    
    .status-secure {
        color: var(--neon-cyan);
        font-weight: 600;
    }
    
    .demo-credentials p {
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }
    
    .demo-credentials strong {
        color: var(--text-primary);
    }
    
    .demo-credentials small {
        color: var(--text-muted);
        font-style: italic;
        display: block;
        margin-top: 1rem;
        line-height: 1.4;
    }
    
    @media (max-width: 768px) {
        .admin-login-container {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .login-panel {
            padding: 2rem;
        }
        
        .admin-logo h1 {
            font-size: 1.5rem;
        }
        
        .admin-logo i {
            font-size: 2rem;
        }
    }
    </style>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('password-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'fas fa-eye';
        }
    }
    
    // Auto-focus on username field
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('username').focus();
    });
    
    // Add enter key support
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.querySelector('.login-form').submit();
        }
    });
    </script>
</body>
</html>