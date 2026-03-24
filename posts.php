<?php
$page_title = 'All Posts';
$active_page = 'posts';
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = getDB();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    redirect('posts.php', 'Post deleted successfully!');
}

// Filters
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search = $_GET['s'] ?? '';

$query = "SELECT p.*, c.name as category_name, u.username FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}
if ($category_filter) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}
if ($search) {
    $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.created_at DESC";
$posts = $db->prepare($query);
$posts->execute($params);
$posts = $posts->fetchAll();

$categories = $db->query("SELECT * FROM categories")->fetchAll();

include_once 'partials/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800;">Posts Management</h1>
        <p style="color: var(--text-muted);">View, edit, and organize your site's content.</p>
    </div>
    <a href="post-edit.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Post
    </a>
</div>

<?php displayFlash(); ?>

<!-- Filters Card -->
<div class="card" style="margin-bottom: 30px; padding: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; display: block;">Search</label>
            <input type="text" name="s" class="form-control" value="<?php echo $search; ?>" placeholder="Search by title or content...">
        </div>
        <div style="width: 150px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; display: block;">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="draft" <?php echo ($status_filter == 'draft') ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo ($status_filter == 'published') ? 'selected' : ''; ?>>Published</option>
                <option value="scheduled" <?php echo ($status_filter == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
            </select>
        </div>
        <div style="width: 150px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; display: block;">Category</label>
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="posts.php" class="btn" style="background: var(--bg-body); border: 1px solid var(--border);">Reset</a>
    </form>
</div>

<!-- All Posts Table -->
<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox"></th>
                    <th>Post Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Views</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><input type="checkbox"></td>
                    <td>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <?php if ($post['featured_image']): ?>
                                <img src="../uploads/<?php echo $post['featured_image']; ?>" style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 45px; height: 45px; border-radius: 8px; background: var(--bg-body); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ccc;">
                                    <i class="far fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <a href="post-edit.php?id=<?php echo $post['id']; ?>" style="font-weight: 700; color: var(--text-main);"><?php echo $post['title']; ?></a>
                                <div style="font-size: 12px; color: var(--text-muted);"><?php echo $post['slug']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td><div style="font-weight: 500; font-size: 14px;"><?php echo $post['username']; ?></div></td>
                    <td><span style="padding: 5px 12px; background: var(--bg-body); border-radius: 20px; font-size: 12px; font-weight: 600;"><?php echo $post['category_name'] ?: 'Uncategorized'; ?></span></td>
                    <td>
                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; <?php echo ($post['status'] == 'published') ? 'background: rgba(46, 196, 182, 0.1); color: var(--success);' : 'background: rgba(128, 129, 145, 0.1); color: var(--text-muted);'; ?>">
                            <?php echo ucfirst($post['status']); ?>
                        </span>
                    </td>
                    <td style="font-size: 13px; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                    <td style="font-weight: 700;"><?php echo number_format($post['views']); ?></td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                            <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn" style="padding: 5px 10px; background: rgba(67, 97, 238, 0.1); color: var(--primary); border: none;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?')" class="btn" style="padding: 5px 10px; background: rgba(230, 57, 70, 0.1); color: var(--danger); border: none;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; if (empty($posts)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px; color: var(--text-muted);">No posts found matching your criteria.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'partials/footer.php'; ?>
