<?php
$page_title = 'Categories';
$active_page = 'categories';
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = getDB();

// Handle Create/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($name);
    $parent_id = (int)$_POST['parent_id'] ?: null;
    $description = clean($_POST['description']);
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("UPDATE categories SET parent_id = ?, name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->execute([$parent_id, $name, $slug, $description, $id]);
        redirect('categories.php', 'Category updated successfully!');
    } else {
        $stmt = $db->prepare("INSERT INTO categories (parent_id, name, slug, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$parent_id, $name, $slug, $description]);
        redirect('categories.php', 'Category created successfully!');
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    redirect('categories.php', 'Category deleted successfully!');
}

$categories = $db->query("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.name ASC")->fetchAll();
$edit_cat = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    foreach ($categories as $c) {
        if ($c['id'] == $id) { $edit_cat = $c; break; }
    }
}

include_once 'partials/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-size: 24px; font-weight: 800;">Categories</h1>
    <p style="color: var(--text-muted);">Organize your posts into hierarchical categories.</p>
</div>

<?php displayFlash(); ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    <!-- Form Card -->
    <div class="card">
        <h3 style="font-weight: 700; margin-bottom: 20px;"><?php echo $edit_cat ? 'Edit Category' : 'Add New Category'; ?></h3>
        <form action="" method="POST">
            <?php if ($edit_cat): ?>
                <input type="hidden" name="id" value="<?php echo $edit_cat['id']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $edit_cat['name'] ?? ''; ?>" placeholder="e.g. Technology" required>
            </div>
            <div class="form-group">
                <label>Slug (Optional)</label>
                <input type="text" name="slug" class="form-control" value="<?php echo $edit_cat['slug'] ?? ''; ?>" placeholder="category-slug">
            </div>
            <div class="form-group">
                <label>Parent Category</label>
                <select name="parent_id" class="form-control">
                    <option value="">None (Top Level)</option>
                    <?php foreach ($categories as $cat): ?>
                        <?php if ($edit_cat && ($cat['id'] == $edit_cat['id'])) continue; ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($edit_cat['parent_id']) && $edit_cat['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" class="form-control" rows="4"><?php echo $edit_cat['description'] ?? ''; ?></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;"><?php echo $edit_cat ? 'Update' : 'Add Category'; ?></button>
                <?php if ($edit_cat): ?>
                    <a href="categories.php" class="btn" style="background: var(--bg-body); border: 1px solid var(--border);">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Parent</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-main);"><?php echo $cat['name']; ?></td>
                        <td style="color: var(--text-muted); font-size: 13px;"><?php echo $cat['slug']; ?></td>
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">
                                <?php echo $cat['parent_name'] ?: 'None'; ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn" style="padding: 5px 10px; background: rgba(67, 97, 238, 0.1); color: var(--primary); border: none;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Are you sure you want to delete this category?')" class="btn" style="padding: 5px 10px; background: rgba(230, 57, 70, 0.1); color: var(--danger); border: none;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No categories found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once 'partials/footer.php'; ?>
