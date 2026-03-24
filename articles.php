<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$msg = '';

// Handle Delete Article
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("SELECT featured_image FROM articles WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $img = $stmt->fetchColumn();
        
        // Delete image file if exists
        if ($img && file_exists($img)) {
            unlink($img);
        }

        $stmt = $pdo->prepare("DELETE FROM articles WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        header("Location: articles.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $msg = "<p style='color:red; margin-bottom:1rem;'>Error deleting article.</p>";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = "<p style='color:green; margin-bottom:1rem;'>Article deleted successfully.</p>";
}

// Fetch Articles
$articles = [];
try {
    $query = "SELECT a.id, a.title, a.status, a.created_at, c.name as category_name, u.username as author_name 
              FROM articles a 
              LEFT JOIN categories c ON a.category_id = c.id
              LEFT JOIN users u ON a.user_id = u.id
              ORDER BY a.created_at DESC";
    $articles = $pdo->query($query)->fetchAll();
} catch (Exception $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Admin Panel</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        .table th { background: var(--bg-tertiary); font-weight: 600; }
        .table tr:hover { background: var(--bg-secondary); }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-edit { background: #eab308; color: white; margin-right: 0.5rem;}
        .status-badge { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .status-published { background: #dcfce7; color: #166534; }
        .status-draft { background: #fef9c3; color: #854d0e; }
        [data-theme="dark"] .status-published { background: rgba(22, 101, 52, 0.4); color: #4ade80;}
        [data-theme="dark"] .status-draft { background: rgba(133, 77, 14, 0.4); color: #fde047;}
    </style>
</head>
<body data-theme="dark">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">Knowledge<span>-Admin</span></div>
        <nav class="sidebar-menu">
            <a href="index.php" class="sidebar-link">Dashboard</a>
            <a href="articles.php" class="sidebar-link active">Articles</a>
            <a href="categories.php" class="sidebar-link">Categories</a>
            <a href="users.php" class="sidebar-link">Users</a>
            <a href="settings.php" class="sidebar-link">Settings</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div><button id="sidebar-toggle" style="background:none; border:none; color:var(--text-primary); font-size:1.5rem; cursor:pointer;" class="btn">☰</button></div>
            <div class="header-right">
                <button id="theme-toggle" class="btn" style="background:none; color:var(--text-primary);">Toggle Theme</button>
                <a href="logout.php" class="btn btn-primary" style="padding:0.25rem 0.75rem;">Logout</a>
            </div>
        </header>

        <div class="content-area">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <h1 class="page-title" style="margin-bottom:0;">Manage Articles</h1>
                <a href="article-edit.php" class="btn btn-primary">+ Add New Article</a>
            </div>
            
            <?php echo $msg; ?>

            <div class="card">
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($articles)): ?>
                            <tr><td colspan="6" style="text-align:center;">No articles found.</td></tr>
                            <?php else: ?>
                                <?php foreach($articles as $art): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($art['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($art['author_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($art['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $art['status'] == 'published' ? 'status-published' : 'status-draft'; ?>">
                                            <?php echo ucfirst($art['status']); ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--text-secondary);"><?php echo date('M d, Y', strtotime($art['created_at'])); ?></td>
                                    <td style="text-align:right;">
                                        <a href="article-edit.php?id=<?php echo $art['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                        <a href="articles.php?delete=<?php echo $art['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this article completely?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="js/admin-script.js"></script>
</body>
</html>