<?php
/**
 * Frontend Configuration & Database Connection
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'admin_panel_db');

function getDB() {
    static $pdo;
    if (!$pdo) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // --- One-Time Auto-Setup & Sync Logic ---
            // 1. Ensure 'posts' table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `posts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `category_id` int(11) DEFAULT NULL,
                `title` varchar(255) NOT NULL,
                `slug` varchar(255) NOT NULL,
                `content` longtext NOT NULL,
                `excerpt` text DEFAULT NULL,
                `featured_image` varchar(255) DEFAULT NULL,
                `status` enum('draft','published','scheduled') DEFAULT 'draft',
                `publish_at` datetime DEFAULT NULL,
                `is_featured` tinyint(1) DEFAULT 0,
                `meta_title` varchar(255) DEFAULT NULL,
                `meta_description` text DEFAULT NULL,
                `views` int(11) DEFAULT 0,
                `created_at` timestamp DEFAULT current_timestamp(),
                `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 2. Ensure 'categories' table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `parent_id` int(11) DEFAULT NULL,
                `name` varchar(100) NOT NULL,
                `slug` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `created_at` timestamp DEFAULT current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 3. Ensure 'settings' table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text DEFAULT NULL,
                `group_name` varchar(50) DEFAULT 'general',
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 4. Fetch counts
            $checkPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
            if ($checkPosts == 0) {
                // Check if old 'articles' table exists to migrate
                $checkArticles = $pdo->query("SHOW TABLES LIKE 'articles'")->fetch();
                if ($checkArticles) {
                    $articles = $pdo->query("SELECT * FROM articles")->fetchAll();
                    foreach ($articles as $a) {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO posts (user_id, category_id, title, slug, content, featured_image, status, is_featured, created_at) VALUES (?,?,?,?,?,?,?,?,?)");
                        $stmt->execute([
                            $a['user_id'] ?? 1, $a['category_id'] ?? null, $a['title'] ?? 'Untitled', $a['slug'] ?? 'post-'.rand(100,999),
                            $a['content'] ?? '', $a['featured_image'] ?? null, $a['status'] ?? 'published',
                            $a['is_featured'] ?? 0, $a['created_at'] ?? date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
        } catch (PDOException $e) {
            die("Database Table Initialization Failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Global settings helper
function getSetting($key, $default = '') {
    $db = getDB();
    if (!$db) return $default;
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: $default;
    } catch (Exception $e) { return $default; }
}
?>
