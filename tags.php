<?php
$page_title = 'Tags';
$active_page = 'tags';
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
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("UPDATE tags SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $id]);
        redirect('tags.php', 'Tag updated successfully!');
    } else {
        $stmt = $db->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        redirect('tags.php', 'Tag created successfully!');
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM tags WHERE id = ?");
    $stmt->execute([$id]);
    redirect('tags.php', 'Tag deleted successfully!');
}

$tags = $db->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();
$edit_tag = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    foreach ($tags as $t) {
        if ($t['id'] == $id) { $edit_tag = $t; break; }
    }
}

include_once 'partials/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-size: 24px; font-weight: 800;">Tags</h1>
    <p style="color: var(--text-muted);">Manage keywords and tags for your posts.</p>
</div>

<?php displayFlash(); ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    <div class="card">
        <h3 style="font-weight: 700; margin-bottom: 20px;"><?php echo $edit_tag ? 'Edit Tag' : 'Add New Tag'; ?></h3>
        <form action="" method="POST">
            <?php if ($edit_tag): ?>
                <input type="hidden" name="id" value="<?php echo $edit_tag['id']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Tag Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $edit_tag['name'] ?? ''; ?>" placeholder="e.g. Tutorial" required>
            </div>
            <div class="form-group">
                <label>Slug (Optional)</label>
                <input type="text" name="slug" class="form-control" value="<?php echo $edit_tag['slug'] ?? ''; ?>" placeholder="tag-slug">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;"><?php echo $edit_tag ? 'Update' : 'Add Tag'; ?></button>
                <?php if ($edit_tag): ?>
                    <a href="tags.php" class="btn" style="background: var(--bg-body); border: 1px solid var(--border);">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php foreach ($tags as $tag): ?>
            <div style="background: var(--bg-body); padding: 10px 20px; border-radius: 30px; border: 1px solid var(--border); display: flex; align-items: center; gap: 15px;">
                <span style="font-weight: 700; color: var(--text-main);"><?php echo $tag['name']; ?></span>
                <div style="display: flex; gap: 5px;">
                    <a href="?edit=<?php echo $tag['id']; ?>" style="color: var(--primary); font-size: 12px;"><i class="fas fa-edit"></i></a>
                    <a href="?delete=<?php echo $tag['id']; ?>" onclick="return confirm('Delete this tag?')" style="color: var(--danger); font-size: 12px;"><i class="fas fa-trash"></i></a>
                </div>
            </div>
            <?php endforeach; if (empty($tags)): ?>
                <p style="color: var(--text-muted); padding: 40px; text-align: center; width: 100%;">No tags found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'partials/footer.php'; ?>
