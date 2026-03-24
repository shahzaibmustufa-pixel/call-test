<?php
require_once 'config.php';
$db = getDB();

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!$slug) { header("Location: index.php"); exit; }

// Fetch Post
$stmt = $db->prepare("SELECT p.*, c.name as category_name, u.username as author_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.user_id = u.id WHERE p.slug = ? AND p.status = 'published'");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) { header("Location: index.php"); exit; }

// Update Views
$db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

// Fetch Related Articles (from same category)
$stmt = $db->prepare("SELECT title, slug, created_at FROM posts WHERE category_id = ? AND id != ? AND status = 'published' LIMIT 3");
$stmt->execute([$post['category_id'], $post['id']]);
$related = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Knowledge-hud</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .article-content { font-size: 1.1rem; line-height: 1.8; color: var(--text-secondary); }
        .article-content img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 2rem 0; }
        .article-content h2, .article-content h3 { color: var(--text-primary); margin: 2.5rem 0 1rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">Knowledge<span>-hud</span></a>
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="blog.php" class="nav-link">Blog</a></li>
                <li><a href="categories.php" class="nav-link">Categories</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
        </div>
    </nav>

    <main class="section-padding container" style="padding-top: 120px; max-width: 900px; margin: 0 auto;">
        <span class="blog-category" style="color: var(--accent-primary); margin-bottom: 1rem; display: inline-block; font-weight: 600; text-transform: uppercase;">
            <?php echo htmlspecialchars($post['category_name'] ?: 'General'); ?>
        </span>
        <h1 style="font-size: 3rem; margin-bottom: 1.5rem; line-height: 1.2;"><?php echo htmlspecialchars($post['title']); ?></h1>
        
        <div class="article-meta" style="display: flex; gap: 2rem; padding: 1.5rem 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); margin: 2rem 0; font-size: 0.95rem; color: var(--text-tertiary);">
            <span>By <strong><?php echo htmlspecialchars($post['author_name'] ?: 'Admin'); ?></strong></span>
            <span>Published on <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
            <span><?php echo $post['views']; ?> Views</span>
        </div>

        <?php if ($post['featured_image']): ?>
        <img src="uploads/<?php echo $post['featured_image']; ?>" alt="Featured Image" style="width: 100%; border-radius: 1rem; margin-bottom: 3rem;">
        <?php endif; ?>

        <div class="article-content">
            <?php echo $post['content']; // HTML from Quill/TinyMCE ?>
        </div>

        <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid var(--border-color);">
            <h3 style="margin-bottom: 2rem;">Related <span class="text-gradient">Articles</span></h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <?php foreach($related as $rel): ?>
                <a href="article.php?slug=<?php echo $rel['slug']; ?>" style="text-decoration: none; color: inherit; padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.75rem; border: 1px solid var(--border-color);">
                    <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($rel['title']); ?></h4>
                    <span style="font-size: 0.85rem; color: var(--text-tertiary);"><?php echo date('M d, Y', strtotime($rel['created_at'])); ?></span>
                </a>
                <?php endforeach; if (empty($related)) echo "<p style='color: var(--text-tertiary);'>No related articles found.</p>"; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container" style="text-align: center; padding: 4rem 0;">
            <p>&copy; <?php echo date('Y'); ?> Knowledge-hud. All rights reserved.</p>
        </div>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
