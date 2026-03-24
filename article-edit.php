<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$msg = '';
$article = [
    'title' => '', 'slug' => '', 'content' => '', 'category_id' => '', 'status' => 'draft',
    'featured_image' => '', 'meta_title' => '', 'meta_description' => '', 'is_featured' => 0,
    'created_at' => date('Y-m-d\TH:i')
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
        // Format date for datetime-local input
        $article['created_at'] = date('Y-m-d\TH:i', strtotime($article['created_at']));
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
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    $content = $_POST['content'];
    $category_id = empty($_POST['category_id']) ? null : $_POST['category_id'];
    $status = $_POST['status'] == 'published' ? 'published' : 'draft';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $created_at = $_POST['created_at'] ? date('Y-m-d H:i:s', strtotime($_POST['created_at'])) : date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'];
    
    $featured_image = $article['featured_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        
        $fileName = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . str_replace(" ", "-", $fileName);
        
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // remove old image if exists
                if($isEdit && !empty($article['featured_image']) && file_exists($article['featured_image'])){
                    unlink($article['featured_image']);
                }
                $featured_image = $target_file;
            } else {
                $msg = "<p style='color:red; padding:1rem; background:#fee2e2; border-radius:0.5rem;'>Failed to upload image.</p>";
            }
        }
    }

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE articles SET title=?, slug=?, content=?, category_id=?, status=?, is_featured=?, featured_image=?, meta_title=?, meta_description=?, created_at=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $category_id, $status, $is_featured, $featured_image, $meta_title, $meta_description, $created_at, $articleId]);
            $msg = "<p style='color:green; padding:1rem; background:#dcfce7; border-radius:0.5rem;'>Article advanced settings updated successfully.</p>";
            
            $article['title']=$title; $article['slug']=$slug; $article['content']=$content; $article['category_id']=$category_id;
            $article['status']=$status; $article['is_featured']=$is_featured; $article['featured_image']=$featured_image; 
            $article['meta_title']=$meta_title; $article['meta_description']=$meta_description; $article['created_at']=date('Y-m-d\TH:i', strtotime($created_at));

        } else {
            $stmt = $pdo->prepare("INSERT INTO articles (user_id, category_id, title, slug, content, featured_image, status, is_featured, meta_title, meta_description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $title, $slug, $content, $featured_image, $status, $is_featured, $meta_title, $meta_description, $created_at]);
            $newId = $pdo->lastInsertId();
            header("Location: article-edit.php?id=" . $newId . "&msg=created");
            exit;
        }
    } catch (PDOException $e) {
        $msg = "<p style='color:red; padding:1rem; background:#fee2e2; border-radius:0.5rem;'>Error saving article. Slug might already exist.</p>";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'created') {
    $msg = "<p style='color:green; padding:1rem; background:#dcfce7; border-radius:0.5rem; color:#166534;'>Article created successfully with advanced options.</p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Article Editor - Admin Panel</title>
    <link rel="stylesheet" href="css/admin-style.css">
    
    <!-- TinyMCE Rich Text Editor (Self-hosted/Open Source alternative via CDNJS, no API key required) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#rich-content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 600,
        skin: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "oxide-dark" : "oxide"),
        content_css: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "default"),
        branding: false,
        promotion: false,
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
      });

      document.addEventListener('DOMContentLoaded', function() {
          const form = document.querySelector('form');
          if (form) {
              form.addEventListener('submit', function() {
                  if (tinymce.get('rich-content')) {
                      document.getElementById('rich-content').value = tinymce.get('rich-content').getContent();
                  }
              });
          }
      });
    </script>

    <style>
        .layout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-primary); color: var(--text-primary); font-family: inherit; }
        .form-control:focus { outline: none; border-color: var(--accent-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .switch { position: relative; display: inline-block; width: 50px; height: 24px; vertical-align: middle; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--accent-primary); }
        input:checked + .slider:before { transform: translateX(26px); }
        .seo-preview { padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-primary); }
        .seo-preview h4 { color: #1a0dab; font-family: arial, sans-serif; font-size: 20px; font-weight: 400; margin-bottom: 2px; }
        [data-theme="dark"] .seo-preview h4 { color: #8ab4f8; }
        .seo-preview .url { color: #006621; font-family: arial, sans-serif; font-size: 14px; margin-bottom: 4px; }
        [data-theme="dark"] .seo-preview .url { color: #81c995; }
        .seo-preview p { color: #545454; font-family: arial, sans-serif; font-size: 14px; line-height: 1.57; }
        [data-theme="dark"] .seo-preview p { color: #bdc1c6; }
        @media(max-width: 1200px) { .layout-grid { grid-template-columns: 1fr; } }
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
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title" style="margin-bottom:0.5rem; font-size: 2rem;"><?php echo $isEdit ? 'Advanced Editor: ' . htmlspecialchars($article['title']) : 'Write New Article'; ?></h1>
                    <p style="color:var(--text-secondary);">Full control over content, SEO, scheduling, and featured status.</p>
                </div>
                <a href="articles.php" class="btn btn-outline" style="border:1px solid var(--border-color); color:var(--text-primary);">Cancel & Back</a>
            </div>
            
            <?php if($msg) echo "<div style='margin-bottom:2rem;'>$msg</div>"; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="layout-grid">
                    
                    <!-- Left Column: Main Editor & SEO -->
                    <div>
                        <div class="card" style="padding: 2.5rem; margin-bottom: 2rem;">
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.75rem; font-weight:600; font-size:1.1rem;">Article Title <span style="color:red;">*</span></label>
                                <input type="text" name="title" id="title-input" class="form-control" required value="<?php echo htmlspecialchars($article['title']); ?>" style="font-size:1.25rem; font-weight:500; padding:1rem;" placeholder="Enter an engaging title...">
                            </div>
                            
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.75rem; font-weight:600; font-size:1.1rem;">Rich Content Editor <span style="color:red;">*</span></label>
                                <textarea name="content" id="rich-content"><?php echo htmlspecialchars($article['content']); ?></textarea>
                            </div>
                        </div>

                        <div class="card" style="padding: 2.5rem;">
                            <h3 style="margin-bottom:1.5rem; font-size:1.25rem; border-bottom:1px solid var(--border-color); padding-bottom:1rem;">Advanced SEO & Search Formatting</h3>
                            
                            <div class="form-group">
                                <label style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-weight:600;">
                                    Meta Title <span id="title-count" style="color:var(--text-tertiary); font-weight:normal;">0/60</span>
                                </label>
                                <input type="text" name="meta_title" id="meta-title" class="form-control" value="<?php echo htmlspecialchars($article['meta_title']); ?>" placeholder="Optimal title for search engines...">
                            </div>

                            <div class="form-group">
                                <label style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-weight:600;">
                                    Meta Description <span id="desc-count" style="color:var(--text-tertiary); font-weight:normal;">0/160</span>
                                </label>
                                <textarea name="meta_description" id="meta-desc" class="form-control" style="min-height:100px; resize:vertical;" placeholder="Write a compelling description that encourages clicks..."><?php echo htmlspecialchars($article['meta_description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">SEO Custom Slug</label>
                                <input type="text" name="slug" id="slug-input" class="form-control" value="<?php echo htmlspecialchars($article['slug']); ?>" placeholder="e.g. general-artificial-intelligence-future">
                                <small style="color:var(--text-tertiary); display:block; margin-top:0.5rem;">Leave empty to auto-generate from the title. Must use hyphens, no spaces.</small>
                            </div>

                            <div class="form-group" style="margin-top:2rem;">
                                <label style="display:block; margin-bottom:1rem; font-weight:600;">Google Search Preview</label>
                                <div class="seo-preview">
                                    <h4 id="preview-title"><?php echo $article['meta_title'] ? htmlspecialchars($article['meta_title']) : 'Your Meta Title Will Appear Here'; ?></h4>
                                    <div class="url">https://knowledge-hud.com/article/<span id="preview-slug"><?php echo $article['slug'] ? htmlspecialchars($article['slug']) : 'your-slug-here'; ?></span></div>
                                    <p id="preview-desc"><?php echo $article['meta_description'] ? htmlspecialchars($article['meta_description']) : 'Your meta description will appear here. It should be compelling and summarize the article perfectly to increase CTR.'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings & Publishing -->
                    <div>
                        <div class="card" style="margin-bottom: 1.5rem; border-top: 4px solid var(--accent-primary);">
                            <h3 style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">Publishing Engine</h3>
                            
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Visibility Status</label>
                                <select name="status" class="form-control" style="background-color: var(--bg-tertiary); font-weight:600;">
                                    <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft (Hidden)</option>
                                    <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Published (Live)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Article Schedule (Date & Time)</label>
                                <input type="datetime-local" name="created_at" class="form-control" value="<?php echo $article['created_at']; ?>">
                                <small style="color:var(--text-tertiary); display:block; margin-top:0.5rem;">Set a future date to schedule publication automatically.</small>
                            </div>
                            
                            <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-top: 1.5rem;">
                                <div>
                                    <strong style="display:block;">Featured Article</strong>
                                    <small style="color:var(--text-secondary);">Pin this article to the homepage hero section.</small>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="is_featured" <?php echo $article['is_featured'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%; text-align:center; margin-top:1.5rem; font-size:1.1rem; padding:1rem; box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);">
                                <?php echo $isEdit ? 'Save Advanced Options' : 'Create Article'; ?>
                            </button>
                        </div>

                        <div class="card" style="margin-bottom: 1.5rem;">
                            <h3 style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">Categorization</h3>
                            <div class="form-group">
                                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Main Category</label>
                                <select name="category_id" class="form-control">
                                    <option value="">-- Select a Category --</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $article['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div style="margin-top:0.75rem; text-align:right;">
                                    <a href="categories.php" style="color:var(--accent-primary); font-size:0.85rem; text-decoration:underline;">Manage Categories</a>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <h3 style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">Featured Hero Image</h3>
                            <?php if($article['featured_image']): ?>
                                <div style="position:relative; margin-bottom:1rem;">
                                    <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="Featured" style="width:100%; border-radius:0.5rem; border:1px solid var(--border-color); display:block;">
                                    <span style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.7); color:white; padding:4px 8px; border-radius:4px; font-size:12px;">Active Image</span>
                                </div>
                            <?php endif; ?>
                            <div style="border: 2px dashed var(--border-color); padding: 2rem 1rem; text-align: center; border-radius: 0.5rem; background: var(--bg-tertiary);">
                                <label for="file-upload" style="cursor:pointer; color:var(--accent-primary); font-weight:600; text-decoration:underline;">Click to upload replacement</label>
                                <input id="file-upload" type="file" name="image" accept="image/*" style="display:none;">
                                <p style="font-size:0.85rem; color:var(--text-secondary); margin-top:0.5rem; padding:0 2rem;">Recommended size: 1200x630px.<br>Max file size: 5MB.</p>
                                <p id="file-name" style="margin-top:1rem; font-size:0.9rem; font-weight:600; color:var(--text-primary);"></p>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="js/admin-script.js"></script>
    <script>
        // Real-time SEO Preview Update Script
        document.addEventListener('DOMContentLoaded', () => {
            const metaTitle = document.getElementById('meta-title');
            const metaDesc = document.getElementById('meta-desc');
            const slugInput = document.getElementById('slug-input');
            const titleInput = document.getElementById('title-input');
            
            const prevTitle = document.getElementById('preview-title');
            const prevDesc = document.getElementById('preview-desc');
            const prevSlug = document.getElementById('preview-slug');
            
            const countTitle = document.getElementById('title-count');
            const countDesc = document.getElementById('desc-count');

            function updatePreview() {
                const t = metaTitle.value.trim() || titleInput.value.trim() || 'Your Meta Title Will Appear Here';
                const d = metaDesc.value.trim() || 'Your meta description will appear here. It should be compelling and summarize the article perfectly to increase CTR.';
                let s = slugInput.value.trim();
                if(!s && titleInput.value) s = titleInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
                if(!s) s = 'your-slug-here';

                prevTitle.textContent = t;
                prevDesc.textContent = d;
                prevSlug.textContent = s;

                countTitle.textContent = `${metaTitle.value.length}/60`;
                countDesc.textContent = `${metaDesc.value.length}/160`;
                
                countTitle.style.color = metaTitle.value.length > 60 ? 'red' : 'var(--text-tertiary)';
                countDesc.style.color = metaDesc.value.length > 160 ? 'red' : 'var(--text-tertiary)';
            }

            metaTitle.addEventListener('input', updatePreview);
            metaDesc.addEventListener('input', updatePreview);
            slugInput.addEventListener('input', updatePreview);
            titleInput.addEventListener('input', updatePreview);
            
            // File upload name display
            document.getElementById('file-upload').addEventListener('change', function() {
                if(this.files && this.files.length > 0) {
                    document.getElementById('file-name').textContent = "Selected: " + this.files[0].name;
                }
            });
        });
    </script>
</body>
</html>