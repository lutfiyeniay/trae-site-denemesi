<?php
// Database Management Interface - PHPMyAdmin Alternative
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in as admin
if (!isLoggedIn() || $_SESSION['admin_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle table operations
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'truncate':
                if (isset($_POST['table'])) {
                    try {
                        $table = $_POST['table'];
                        // Security: Only allow specific tables
                        $allowed_tables = ['blog_posts', 'admin_users', 'stream_settings', 'site_settings'];
                        if (in_array($table, $allowed_tables)) {
                            $pdo->exec("TRUNCATE TABLE `$table`");
                            $message = "Tablo '$table' başarıyla temizlendi.";
                        } else {
                            $error = "Bu tablo üzerinde işlem yapma yetkiniz yok.";
                        }
                    } catch (PDOException $e) {
                        $error = "Hata: " . $e->getMessage();
                    }
                }
                break;
                
            case 'drop':
                if (isset($_POST['table'])) {
                    try {
                        $table = $_POST['table'];
                        $allowed_tables = ['blog_posts', 'admin_users', 'stream_settings', 'site_settings'];
                        if (in_array($table, $allowed_tables)) {
                            $pdo->exec("DROP TABLE `$table`");
                            $message = "Tablo '$table' başarıyla silindi.";
                        } else {
                            $error = "Bu tablo üzerinde işlem yapma yetkiniz yok.";
                        }
                    } catch (PDOException $e) {
                        $error = "Hata: " . $e->getMessage();
                    }
                }
                break;
                
            case 'sql':
                if (isset($_POST['sql_query'])) {
                    try {
                        $query = trim($_POST['sql_query']);
                        // Basic SQL injection protection
                        $dangerous_keywords = ['DROP DATABASE', 'CREATE DATABASE', 'ALTER DATABASE'];
                        foreach ($dangerous_keywords as $keyword) {
                            if (stripos($query, $keyword) !== false) {
                                throw new Exception("Güvenlik nedeniyle bu sorgu çalıştırılamaz.");
                            }
                        }
                        
                        if (stripos($query, 'SELECT') === 0) {
                            $stmt = $pdo->prepare($query);
                            $stmt->execute();
                            $sql_results = $stmt->fetchAll();
                            $message = "Sorgu başarıyla çalıştırıldı. " . count($sql_results) . " kayıt bulundu.";
                        } else {
                            $affected = $pdo->exec($query);
                            $message = "Sorgu başarıyla çalıştırıldı. $affected kayıt etkilendi.";
                        }
                    } catch (Exception $e) {
                        $error = "SQL Hatası: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get all tables
try {
    $tables_stmt = $pdo->query("SHOW TABLES");
    $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $tables = [];
    $error = "Tablolar alınamadı: " . $e->getMessage();
}

// Get selected table data
$selected_table = $_GET['table'] ?? '';
$table_data = [];
$table_structure = [];

if ($selected_table && in_array($selected_table, $tables)) {
    try {
        // Get table data (limit to 100 rows)
        $data_stmt = $pdo->query("SELECT * FROM `$selected_table` LIMIT 100");
        $table_data = $data_stmt->fetchAll();
        
        // Get table structure
        $structure_stmt = $pdo->query("DESCRIBE `$selected_table`");
        $table_structure = $structure_stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Tablo verileri alınamadı: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veritabanı Yönetimi - CyberAdmin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="container">
            <div class="admin-logo">
                <i class="fas fa-database"></i>
                <h1>CYBER<span class="accent">DB</span></h1>
            </div>
            <nav class="admin-menu">
                <a href="dashboard.php" class="admin-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="database.php" class="admin-link active">
                    <i class="fas fa-database"></i>
                    Veritabanı
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

    <main class="admin-main">
        <div class="container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="db-layout">
                <!-- Sidebar with tables -->
                <div class="db-sidebar">
                    <div class="sidebar-header">
                        <h3><i class="fas fa-table"></i> Tablolar</h3>
                    </div>
                    <div class="tables-list">
                        <?php foreach ($tables as $table): ?>
                            <a href="?table=<?php echo urlencode($table); ?>" 
                               class="table-item <?php echo $selected_table === $table ? 'active' : ''; ?>">
                                <i class="fas fa-table"></i>
                                <?php echo htmlspecialchars($table); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Main content -->
                <div class="db-content">
                    <?php if ($selected_table): ?>
                        <div class="table-header">
                            <h2><i class="fas fa-table"></i> <?php echo htmlspecialchars($selected_table); ?></h2>
                            <div class="table-actions">
                                <form method="post" style="display: inline;" onsubmit="return confirm('Bu tabloyu temizlemek istediğinizden emin misiniz?');">
                                    <input type="hidden" name="action" value="truncate">
                                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-trash"></i> Temizle
                                    </button>
                                </form>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Bu tabloyu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!');">
                                    <input type="hidden" name="action" value="drop">
                                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Sil
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Table tabs -->
                        <div class="table-tabs">
                            <button class="tab-btn active" onclick="showTab('data')">
                                <i class="fas fa-list"></i> Veriler
                            </button>
                            <button class="tab-btn" onclick="showTab('structure')">
                                <i class="fas fa-cogs"></i> Yapı
                            </button>
                        </div>

                        <!-- Data tab -->
                        <div id="data-tab" class="tab-content active">
                            <?php if (!empty($table_data)): ?>
                                <div class="table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <?php foreach (array_keys($table_data[0]) as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($table_data as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td><?php echo htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : ''); ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="table-info">
                                    <p><i class="fas fa-info-circle"></i> İlk 100 kayıt gösteriliyor. Toplam: <?php echo count($table_data); ?> kayıt</p>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Bu tabloda henüz veri bulunmuyor.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Structure tab -->
                        <div id="structure-tab" class="tab-content">
                            <?php if (!empty($table_structure)): ?>
                                <div class="table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Alan</th>
                                                <th>Tip</th>
                                                <th>Null</th>
                                                <th>Anahtar</th>
                                                <th>Varsayılan</th>
                                                <th>Ekstra</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($table_structure as $column): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($column['Field']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                                    <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                                    <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                                    <td><?php echo htmlspecialchars($column['Default']); ?></td>
                                                    <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="welcome-state">
                            <i class="fas fa-database"></i>
                            <h3>Veritabanı Yönetimi</h3>
                            <p>Sol taraftan bir tablo seçerek verilerini görüntüleyebilirsiniz.</p>
                        </div>
                    <?php endif; ?>

                    <!-- SQL Query Section -->
                    <div class="sql-section">
                        <div class="section-header">
                            <h3><i class="fas fa-code"></i> SQL Sorgusu Çalıştır</h3>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="sql">
                            <div class="sql-editor">
                                <textarea name="sql_query" id="sql-query" placeholder="SELECT * FROM blog_posts LIMIT 10;"><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : ''; ?></textarea>
                            </div>
                            <div class="sql-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Çalıştır
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearSQL()">
                                    <i class="fas fa-eraser"></i> Temizle
                                </button>
                            </div>
                        </form>

                        <?php if (isset($sql_results) && !empty($sql_results)): ?>
                            <div class="sql-results">
                                <h4><i class="fas fa-table"></i> Sorgu Sonuçları</h4>
                                <div class="table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <?php foreach (array_keys($sql_results[0]) as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sql_results as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td><?php echo htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : ''); ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
    <script>
        // Initialize CodeMirror for SQL editor
        const sqlEditor = CodeMirror.fromTextArea(document.getElementById('sql-query'), {
            mode: 'text/x-sql',
            theme: 'monokai',
            lineNumbers: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentWithTabs: true,
            smartIndent: true,
            lineWrapping: true
        });

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function clearSQL() {
            sqlEditor.setValue('');
        }
    </script>

    <style>
    /* Database Management Styles */
    .db-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }

    .db-sidebar {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--neon-blue);
        border-radius: 10px;
        padding: 1.5rem;
        height: fit-content;
    }

    .sidebar-header h3 {
        color: var(--neon-blue);
        font-family: 'Orbitron', monospace;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .tables-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .table-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .table-item:hover {
        background: rgba(0, 255, 255, 0.1);
        color: var(--neon-cyan);
        border-color: var(--neon-cyan);
    }

    .table-item.active {
        background: rgba(255, 0, 128, 0.2);
        color: var(--neon-pink);
        border-color: var(--neon-pink);
    }

    .db-content {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--neon-blue);
        border-radius: 10px;
        padding: 2rem;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--neon-blue);
    }

    .table-header h2 {
        color: var(--neon-blue);
        font-family: 'Orbitron', monospace;
        margin: 0;
    }

    .table-actions {
        display: flex;
        gap: 1rem;
    }

    .table-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .tab-btn {
        padding: 0.75rem 1.5rem;
        background: transparent;
        border: 1px solid var(--neon-blue);
        color: var(--text-secondary);
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Rajdhani', sans-serif;
    }

    .tab-btn:hover {
        background: rgba(0, 255, 255, 0.1);
        color: var(--neon-cyan);
    }

    .tab-btn.active {
        background: var(--neon-blue);
        color: var(--primary-bg);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .table-wrapper {
        overflow-x: auto;
        margin-bottom: 1rem;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 5px;
        overflow: hidden;
    }

    .data-table th,
    .data-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .data-table th {
        background: rgba(0, 255, 255, 0.2);
        color: var(--neon-cyan);
        font-family: 'Orbitron', monospace;
        font-weight: 600;
    }

    .data-table td {
        color: var(--text-primary);
        font-family: 'Rajdhani', sans-serif;
    }

    .data-table tr:hover {
        background: rgba(255, 0, 128, 0.1);
    }

    .table-info {
        color: var(--text-secondary);
        font-style: italic;
    }

    .empty-state,
    .welcome-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-secondary);
    }

    .empty-state i,
    .welcome-state i {
        font-size: 3rem;
        color: var(--neon-blue);
        margin-bottom: 1rem;
    }

    .sql-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--neon-blue);
    }

    .section-header h3 {
        color: var(--neon-pink);
        font-family: 'Orbitron', monospace;
        margin-bottom: 1rem;
    }

    .sql-editor {
        margin-bottom: 1rem;
    }

    .sql-editor .CodeMirror {
        height: 200px;
        border: 1px solid var(--neon-blue);
        border-radius: 5px;
        font-family: 'Courier New', monospace;
    }

    .sql-actions {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .sql-results {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sql-results h4 {
        color: var(--neon-green);
        font-family: 'Orbitron', monospace;
        margin-bottom: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        text-transform: uppercase;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: var(--neon-blue);
        color: var(--primary-bg);
    }

    .btn-primary:hover {
        background: var(--neon-cyan);
        box-shadow: 0 0 20px var(--neon-cyan);
    }

    .btn-secondary {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--text-secondary);
    }

    .btn-secondary:hover {
        background: var(--text-secondary);
        color: var(--primary-bg);
    }

    .btn-warning {
        background: var(--neon-yellow);
        color: var(--primary-bg);
    }

    .btn-warning:hover {
        background: #ffff00;
        box-shadow: 0 0 20px var(--neon-yellow);
    }

    .btn-danger {
        background: var(--neon-pink);
        color: var(--primary-bg);
    }

    .btn-danger:hover {
        background: #ff0080;
        box-shadow: 0 0 20px var(--neon-pink);
    }

    .alert {
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background: rgba(0, 255, 0, 0.2);
        border: 1px solid var(--neon-green);
        color: var(--neon-green);
    }

    .alert-error {
        background: rgba(255, 0, 128, 0.2);
        border: 1px solid var(--neon-pink);
        color: var(--neon-pink);
    }

    @media (max-width: 768px) {
        .db-layout {
            grid-template-columns: 1fr;
        }
        
        .table-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        
        .table-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
    </style>
</body>
</html>