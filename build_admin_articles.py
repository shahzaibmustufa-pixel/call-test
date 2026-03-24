import os

# Create uploads directory for featured images
os.makedirs('admin/uploads', exist_ok=True)

# 1. admin/articles.php
articles_content = """<?php
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
</html>"""
with open('admin/articles.php', 'w', encoding='utf-8') as f: f.write(articles_content)


# 2. admin/article-edit.php
edit_content = """<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$msg = '';
$article = [
    'title' => '', 'slug' => '', 'content' => '', 'category_id' => '', 'status' => 'draft',
    'featured_image' => '', 'meta_title' => '', 'meta_description' => ''
];
$isEdit = false;
$articleId = null;

// Fetch Categories for Dropdown
$categories = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {}

// Load existing article if ID provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $articleId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$articleId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $article = $existing;
    } else {
        header("Location: articles.php");
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    if (empty($slug)) {
        // Auto-generate slug if empty
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    $content = $_POST['content'];
    $category_id = empty($_POST['category_id']) ? null : $_POST['category_id'];
    $status = $_POST['status'] == 'published' ? 'published' : 'draft';
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $user_id = $_SESSION['user_id'];
    
    // Image Upload Logic (Basic)
    $featured_image = $article['featured_image']; // Keep old by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        
        $fileName = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . str_replace(" ", "-", $fileName);
        
        // Basic image validation
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $featured_image = $target_file;
            } else {
                $msg = "<p style='color:red;'>Failed to upload image.</p>";
            }
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE articles SET title=?, slug=?, content=?, category_id=?, status=?, featured_image=?, meta_title=?, meta_description=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $category_id, $status, $featured_image, $meta_title, $meta_description, $articleId]);
            $msg = "<p style='color:green;'>Article updated successfully.</p>";
            
            // update local array for display right away
            $article['title']=$title; $article['slug']=$slug; $article['content']=$content; $article['category_id']=$category_id;
            $article['status']=$status; $article['featured_image']=$featured_image; $article['meta_title']=$meta_title; $article['meta_description']=$meta_description;

        } else {
            $stmt = $pdo->prepare("INSERT INTO articles (user_id, category_id, title, slug, content, featured_image, status, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $title, $slug, $content, $featured_image, $status, $meta_title, $meta_description]);
            $newId = $pdo->lastInsertId();
            header("Location: article-edit.php?id=" . $newId . "&msg=created");
            exit;
        }
    } catch (PDOException $e) {
        $msg = "<p style='color:red;'>Error saving article. Slug might already exist.</p>";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'created') {
    $msg = "<p style='color:green;'>Article created successfully.</p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit Article' : 'New Article'; ?> - Admin Panel</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .layout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-primary); color: var(--text-primary); font-family: inherit; }
        textarea.form-control { min-height: 300px; resize: vertical; line-height: 1.6; }
        @media(max-width: 992px) { .layout-grid { grid-template-columns: 1fr; } }
        /* Simple mock styles for editor buttons since we don't have TinyMCE yet */
        .editor-toolbar { background: var(--bg-tertiary); padding: 0.5rem; border: 1px solid var(--border-color); border-bottom: none; border-radius: 0.5rem 0.5rem 0 0; display:flex; gap:0.5rem; }
        .editor-btn { background: var(--bg-primary); color:var(--text-primary); border: 1px solid var(--border-color); padding:0.25rem 0.5rem; border-radius:0.25rem; cursor:pointer;}
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
                <h1 class="page-title" style="margin-bottom:0;"><?php echo $isEdit ? 'Edit Article' : 'Write New Article'; ?></h1>
                <a href="articles.php" class="btn btn-outline" style="border:1px solid var(--border-color); color:var(--text-primary);">Back to Articles</a>
            </div>
            
            <?php echo $msg; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="layout-grid">
                    
                    <!-- Left Column: Main Editor -->
                    <div class="card" style="padding: 2rem;">
                        <div class="form-group">
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Title *</label>
                            <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($article['title']); ?>" style="font-size:1.2rem;">
                        </div>
                        
                        <div class="form-group">
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Content *</label>
                            <!-- Basic Toolbar Mock -->
                            <div class="editor-toolbar">
                                <button type="button" class="editor-btn"><strong>B</strong></button>
                                <button type="button" class="editor-btn"><em>I</em></button>
                                <button type="button" class="editor-btn"><u>U</u></button>
                                <button type="button" class="editor-btn">H2</button>
                                <button type="button" class="editor-btn">H3</button>
                                <button type="button" class="editor-btn">Link</button>
                            </div>
                            <textarea name="content" class="form-control" style="border-radius: 0 0 0.5rem 0.5rem;" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                            <p style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.5rem;">Accepts basic HTML tagging. A full Rich Text Editor (like TinyMCE) can be integrated here.</p>
                        </div>
                    </div>

                    <!-- Right Column: Meta & Sidebar Settings -->
                    <div>
                        <div class="card" style="margin-bottom: 1.5rem;">
                            <h3 style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">Publishing Options</h3>
                            
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem;">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem;">Category</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Uncategorized</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $article['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%; text-align:center; margin-top:1rem; font-size:1.1rem; padding:0.8rem;">
                                <?php echo $isEdit ? 'Save Changes' : 'Draft / Publish Article'; ?>
                            </button>
                        </div>

                        <div class="card" style="margin-bottom: 1.5rem;">
                            <h3 style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">Featured Image</h3>
                            <?php if($article['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="Featured" style="width:100%; border-radius:0.5rem; margin-bottom:1rem; border:1px solid var(--border-color);">
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*" style="padding:0.5rem;">
                        </div>

                        <div class="card">
                            <h3 style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">SEO Settings</h3>
                            
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem;">URL Slug (Optional)</label>
                                <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($article['slug']); ?>" placeholder="Leave empty to auto-generate">
                            </div>

                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem;">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($article['meta_title']); ?>">
                            </div>

                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem;">Meta Description</label>
                                <textarea name="meta_description" class="form-control" style="min-height:100px;"><?php echo htmlspecialchars($article['meta_description']); ?></textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </main>
    <script src="js/admin-script.js"></script>
</body>
</html>"""
with open('admin/article-edit.php', 'w', encoding='utf-8') as f: f.write(edit_content)

print("Article management phase created successfully.")
