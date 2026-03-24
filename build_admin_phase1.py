import os

# Create directories
dirs = [
    'admin',
    'admin/includes',
    'admin/css',
    'admin/js'
]
for d in dirs:
    os.makedirs(d, exist_ok=True)

# 1. database.sql
sql_content = """-- Knowledge-hud Database Schema

CREATE DATABASE IF NOT EXISTS `knowledge_hud`;
USE `knowledge_hud`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@knowledgehud.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','spam') DEFAULT 'pending',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Knowledge-hud'),
('contact_email', 'contact@knowledgehud.com')
ON DUPLICATE KEY UPDATE id=id;
"""
with open('database.sql', 'w', encoding='utf-8') as f: f.write(sql_content)

# 2. admin/includes/config.php
config_content = """<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'knowledge_hud');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect to database. " . $e->getMessage() . "<br>Please ensure the database '" . DB_NAME . "' exists or import database.sql.");
}
?>"""
with open('admin/includes/config.php', 'w', encoding='utf-8') as f: f.write(config_content)

# 3. admin/includes/auth.php
auth_content = """<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect if already logged in (for login page)
function restrictIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}
?>"""
with open('admin/includes/auth.php', 'w', encoding='utf-8') as f: f.write(auth_content)

# 4. admin/login.php
login_content = """<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

restrictIfLoggedIn();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Knowledge-hud</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--bg-tertiary); }
        .login-box { background: var(--card-bg); padding: 3rem; border-radius: 1rem; box-shadow: var(--shadow-lg); width: 100%; max-width: 400px; border: 1px solid var(--border-color); }
        .login-box h2 { text-align: center; margin-bottom: 2rem; color: var(--text-primary); }
        .form-group { margin-bottom: 1.5rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-primary); color: var(--text-primary); }
        .btn-primary { width: 100%; padding: 0.75rem; background: var(--accent-primary); color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
        .btn-primary:hover { background: var(--accent-hover); }
        .error-msg { color: #ef4444; margin-bottom: 1rem; text-align: center; font-size: 0.9rem; }
    </style>
</head>
<body data-theme="dark">
    <div class="login-box">
        <h2>Admin Panel</h2>
        <?php if($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; color:var(--text-secondary);">Username</label>
                <input type="text" name="username" class="form-control" required value="admin">
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; color:var(--text-secondary);">Password</label>
                <input type="password" name="password" class="form-control" required value="admin123">
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>
    </div>
</body>
</html>"""
with open('admin/login.php', 'w', encoding='utf-8') as f: f.write(login_content)

# 5. admin/logout.php
logout_content = """<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>"""
with open('admin/logout.php', 'w', encoding='utf-8') as f: f.write(logout_content)

# 6. admin/css/admin-style.css
css_content = """@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

:root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-tertiary: #64748b;
    --accent-primary: #4f46e5;
    --accent-hover: #4338ca;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --card-bg: #ffffff;
    --sidebar-bg: #1e293b;
    --sidebar-text: #f8fafc;
    --sidebar-hover: #334155;
}

[data-theme="dark"] {
    --bg-primary: #0f172a;
    --bg-secondary: #1e293b;
    --bg-tertiary: #334155;
    --text-primary: #f8fafc;
    --text-secondary: #cbd5e1;
    --text-tertiary: #94a3b8;
    --accent-primary: #6366f1;
    --accent-hover: #818cf8;
    --border-color: #334155;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    --card-bg: #1e293b;
    --sidebar-bg: #0f172a;
    --sidebar-text: #cbd5e1;
    --sidebar-hover: #1e293b;
}

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

body { background-color: var(--bg-secondary); color: var(--text-primary); display: flex; min-height: 100vh; overflow-x: hidden; }

/* Sidebar */
.sidebar { width: 260px; background-color: var(--sidebar-bg); color: var(--sidebar-text); display: flex; flex-direction: column; transition: 0.3s ease; position: fixed; height: 100vh; z-index: 100; border-right: 1px solid var(--border-color); }
.sidebar-header { height: 70px; display: flex; align-items: center; padding: 0 1.5rem; font-size: 1.25rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-menu { flex-grow: 1; padding: 1rem 0; overflow-y: auto; }
.sidebar-link { display: flex; align-items: center; padding: 0.75rem 1.5rem; color: var(--sidebar-text); text-decoration: none; transition: 0.2s; gap: 1rem; }
.sidebar-link:hover, .sidebar-link.active { background-color: var(--sidebar-hover); border-left: 4px solid var(--accent-primary); padding-left: calc(1.5rem - 4px); color: white; }

/* Main Content */
.main-content { flex-grow: 1; margin-left: 260px; display: flex; flex-direction: column; width: calc(100% - 260px); }
.top-header { height: 70px; background-color: var(--bg-primary); display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 90; }
.header-right { display: flex; align-items: center; gap: 1.5rem; }

/* Dashboard Cards */
.content-area { padding: 2rem; }
.page-title { margin-bottom: 2rem; font-size: 1.5rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: var(--card-bg); padding: 1.5rem; border-radius: 0.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1rem; }
.stat-icon { width: 48px; height: 48px; border-radius: 50%; background: rgba(99,102,241,0.1); color: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-details h3 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.stat-details p { color: var(--text-secondary); font-size: 0.9rem; }

/* Components */
.btn { padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; border: none; font-weight: 600; text-decoration: none; display: inline-block; }
.btn-primary { background: var(--accent-primary); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.card { background: var(--card-bg); padding: 1.5rem; border-radius: 0.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); }

@media(max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .main-content { margin-left: 0; width: 100%; }
}
"""
with open('admin/css/admin-style.css', 'w', encoding='utf-8') as f: f.write(css_content)

# 7. admin/js/admin-script.js
js_content = """document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle
    const themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('admin_theme', newTheme);
        });
        
        const savedTheme = localStorage.getItem('admin_theme');
        if (savedTheme) {
            document.body.setAttribute('data-theme', savedTheme);
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.setAttribute('data-theme', 'dark');
        }
    }

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }
});
"""
with open('admin/js/admin-script.js', 'w', encoding='utf-8') as f: f.write(js_content)

# 8. admin/index.php
index_content = """<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Mock stats for dashboard (Can implement real queries later)
// Try real queries if tables exist and populated
$stats = [
    'articles' => 0,
    'categories' => 0,
    'users' => 0,
    'views' => 0
];

try {
    $stats['articles'] = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    $stats['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['views'] = $pdo->query("SELECT SUM(views) FROM articles")->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Tables might be empty or not created yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Knowledge-hud Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body data-theme="dark">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            Knowledge<span>-Admin</span>
        </div>
        <nav class="sidebar-menu">
            <a href="index.php" class="sidebar-link active">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="articles.php" class="sidebar-link">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 7H20M14 13h5"/></svg>
                Articles
            </a>
            <a href="categories.php" class="sidebar-link">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>
            <a href="users.php" class="sidebar-link">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users
            </a>
            <a href="settings.php" class="sidebar-link">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-header">
            <div>
                <button id="sidebar-toggle" style="background:none; border:none; color:var(--text-primary); cursor:pointer; display:none;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div class="header-right">
                <button id="theme-toggle" style="background:none; border:none; color:var(--text-primary); cursor:pointer;" title="Toggle Theme">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span style="font-weight:500;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-outline" style="border: 1px solid var(--border-color); padding: 0.25rem 0.75rem; border-radius:0.25rem; font-size:0.875rem; color:var(--text-secondary);">Logout</a>
                </div>
            </div>
        </header>

        <div class="content-area">
            <h1 class="page-title">Dashboard Overview</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['articles']); ?></h3>
                        <p>Total Articles</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📁</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['categories']); ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['users']); ?></h3>
                        <p>Active Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['views']); ?></h3>
                        <p>Total Article Views</p>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Recent Activity</h3>
                <p style="color: var(--text-secondary);">Welcome to the new Admin Panel. Start by adding new categories and publishing articles!</p>
            </div>
            
        </div>
    </main>

    <script src="js/admin-script.js"></script>
</body>
</html>"""
with open('admin/index.php', 'w', encoding='utf-8') as f: f.write(index_content)

print("Phase 1 files generated successfully.")
