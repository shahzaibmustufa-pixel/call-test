<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = getDB();
$id = $_GET['id'] ?? null;
$post = null;

if ($id) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) redirect('posts.php', 'Post not found.', 'danger');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean($_POST['title']);
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($title);
    $content = $_POST['content']; // Don't clean HTML from editor
    $excerpt = clean($_POST['excerpt']);
    $category_id = (int)$_POST['category_id'] ?: null;
    $status = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $meta_title = clean($_POST['meta_title']);
    $meta_description = clean($_POST['meta_description']);
    
    // Image Upload
    $featured_image = $post['featured_image'] ?? null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $ext;
        $target = '../uploads/' . $file_name;
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
            $featured_image = $file_name;
        }
    }

    if (empty($title)) $errors[] = "Title is required.";

    // --- UNIQUE SLUG CHECK ---
    if (empty($errors)) {
        $check_stmt = $db->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
        $check_stmt->execute([$slug, $id ?: 0]);
        if ($check_stmt->fetch()) {
            $slug .= '-' . rand(10, 99); // Deconflict
        }
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $db->prepare("UPDATE posts SET category_id = ?, title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, status = ?, is_featured = ?, meta_title = ?, meta_description = ? WHERE id = ?");
            $stmt->execute([$category_id, $title, $slug, $content, $excerpt, $featured_image, $status, $is_featured, $meta_title, $meta_description, $id]);
            redirect('posts.php', 'Post updated successfully!');
        } else {
            $stmt = $db->prepare("INSERT INTO posts (user_id, category_id, title, slug, content, excerpt, featured_image, status, is_featured, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $category_id, $title, $slug, $content, $excerpt, $featured_image, $status, $is_featured, $meta_title, $meta_description]);
            redirect('posts.php', 'Post created successfully!');
        }
    }
}

$categories = $db->query("SELECT * FROM categories")->fetchAll();
$page_title = $id ? 'Edit Post' : 'New Post';
$active_page = 'posts';

include_once 'partials/header.php';
?>

<form action="" method="POST" enctype="multipart/form-data">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800;"><?php echo $page_title; ?></h1>
            <p style="color: var(--text-muted);">Manage post content, SEO, and visibility.</p>
        </div>
        <div style="display: flex; gap: 15px;">
            <a href="posts.php" class="btn" style="background: var(--bg-body); border: 1px solid var(--border);">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px;">
        <div class="main-form">
            <div class="card" style="margin-bottom: 30px;">
                <div class="form-group">
                    <label>Post Title</label>
                    <input type="text" name="title" class="form-control" style="font-size: 18px; font-weight: 700;" value="<?php echo $post['title'] ?? ''; ?>" placeholder="Enter title here" required>
                </div>
                <div class="form-group">
                    <label>URL Slug (Optional)</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $post['slug'] ?? ''; ?>" placeholder="post-url-slug">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="editor"><?php echo $post['content'] ?? ''; ?></textarea>
                </div>
            </div>

            <div class="card">
                <h3 style="font-weight: 700; margin-bottom: 20px;">SEO Settings</h3>
                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?php echo $post['meta_title'] ?? ''; ?>" placeholder="Post title for search engines">
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="3" placeholder="Brief summary for search engines"><?php echo $post['meta_description'] ?? ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="3" placeholder="Brief summary for the blog listing"><?php echo $post['excerpt'] ?? ''; ?></textarea>
                </div>
            </div>
        </div>

        <aside class="side-form">
            <div class="card" style="margin-bottom: 30px;">
                <h3 style="font-weight: 700; margin-bottom: 20px;">Publish</h3>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?php echo (isset($post['status']) && $post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo (isset($post['status']) && $post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                        <option value="scheduled" <?php echo (isset($post['status']) && $post['status'] == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_featured" id="is_featured" <?php echo (isset($post['is_featured']) && $post['is_featured']) ? 'checked' : ''; ?>>
                    <label for="is_featured" style="margin: 0;">Mark as Featured</label>
                </div>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Post</button>
            </div>

            <div class="card" style="margin-bottom: 30px;">
                <h3 style="font-weight: 700; margin-bottom: 20px;">Category</h3>
                <select name="category_id" class="form-control">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($post['category_id']) && $post['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="categories.php" style="display: block; margin-top: 10px; font-size: 13px; color: var(--primary);">+ Manage Categories</a>
            </div>

            <div class="card">
                <h3 style="font-weight: 700; margin-bottom: 20px;">Featured Image</h3>
                <?php if (isset($post['featured_image'])): ?>
                    <img src="../uploads/<?php echo $post['featured_image']; ?>" style="width: 100%; border-radius: 12px; margin-bottom: 15px;">
                <?php endif; ?>
                <input type="file" name="featured_image" class="form-control">
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Best size: 1200x630px</p>
            </div>
        </aside>
    </div>
</form>

<!-- TinyMCE CDN (Self-hosted/Open Source alternative via CDNJS, no API key required) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
        content_style: 'body { font-family:Inter,-apple-system,sans-serif; font-size:16px }',
        skin: document.documentElement.getAttribute('data-theme') === 'dark' ? 'oxide-dark' : 'oxide',
        content_css: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default',
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
                if (tinymce.get('editor')) {
                    document.getElementById('editor').value = tinymce.get('editor').getContent();
                }
            });
        }
    });
</script>

<?php include_once 'partials/footer.php'; ?>
