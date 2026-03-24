<?php
$page_title = 'Comments';
$active_page = 'comments';
require_once 'includes/auth_middleware.php';
require_permission('editor');
$db = getDB();

// Bulk / single actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    verify_csrf();
    $ids   = array_map('intval', $_POST['ids'] ?? []);
    $action = $_POST['bulk_action'];
    if (!empty($ids)) {
        $in = implode(',', $ids);
        if ($action === 'approve')      $db->exec("UPDATE comments SET status='approved' WHERE id IN ($in)");
        elseif ($action === 'spam')     $db->exec("UPDATE comments SET status='spam' WHERE id IN ($in)");
        elseif ($action === 'delete')   $db->exec("DELETE FROM comments WHERE id IN ($in)");
    }
    redirect('comments.php', 'Bulk action applied.');
}
if (isset($_GET['approve']))  { $db->prepare("UPDATE comments SET status='approved' WHERE id=?")->execute([(int)$_GET['approve']]); redirect('comments.php','Comment approved.'); }
if (isset($_GET['spam']))     { $db->prepare("UPDATE comments SET status='spam'     WHERE id=?")->execute([(int)$_GET['spam']]);    redirect('comments.php','Marked as spam.'); }
if (isset($_GET['delete']))   { $db->prepare("DELETE FROM comments WHERE id=?")             ->execute([(int)$_GET['delete']]);    redirect('comments.php','Comment deleted.'); }

$status_filter = $_GET['status'] ?? '';
$search        = clean($_GET['s'] ?? '');
$sql    = "SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id WHERE 1=1";
$params = [];
if ($status_filter) { $sql .= " AND c.status = ?"; $params[] = $status_filter; }
if ($search)        { $sql .= " AND (c.name LIKE ? OR c.content LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY c.created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$comments = $stmt->fetchAll();

include_once 'partials/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1 style="font-size:24px;font-weight:800;">Comments</h1>
        <p style="color:var(--text-muted);">Moderate comments — approve, spam, or delete.</p>
    </div>
</div>
<?php displayFlash(); ?>

<!-- Filters -->
<div class="card" style="padding:20px;margin-bottom:25px;">
    <form method="GET" style="display:flex;gap:15px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Search</label>
            <input type="text" name="s" class="form-control" value="<?php echo $search; ?>" placeholder="Search by author or content...">
        </div>
        <div style="width:160px;">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="pending"  <?php echo $status_filter==='pending' ?'selected':''; ?>>Pending</option>
                <option value="approved" <?php echo $status_filter==='approved'?'selected':''; ?>>Approved</option>
                <option value="spam"     <?php echo $status_filter==='spam'    ?'selected':''; ?>>Spam</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="comments.php" class="btn" style="background:var(--bg-body);border:1px solid var(--border);">Reset</a>
    </form>
</div>

<form method="POST">
    <?php csrf_field(); ?>
    <div class="card">
        <div style="display:flex;gap:10px;align-items:center;padding:15px;border-bottom:1px solid var(--border);">
            <input type="checkbox" id="check-all" onchange="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)">
            <label for="check-all" style="font-weight:600;font-size:13px;">Select All</label>
            <select name="bulk_action" class="form-control" style="width:auto;padding:6px 12px;">
                <option value="">Bulk Action</option>
                <option value="approve">Approve</option>
                <option value="spam">Mark as Spam</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding:8px 18px;">Apply</button>
        </div>
        <div class="table-container">
            <table>
                <thead><tr>
                    <th style="width:40px;"></th>
                    <th>Author</th>
                    <th>Comment</th>
                    <th>Post</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($comments as $c): ?>
                <tr>
                    <td><input class="row-check" type="checkbox" name="ids[]" value="<?php echo $c['id']; ?>"></td>
                    <td>
                        <div style="font-weight:600;"><?php echo $c['name']; ?></div>
                        <div style="font-size:12px;color:var(--text-muted);"><?php echo $c['email']; ?></div>
                    </td>
                    <td style="max-width:300px;font-size:13px;"><?php echo mb_strimwidth($c['content'],0,100,'…'); ?></td>
                    <td style="font-size:13px;font-weight:600;"><?php echo $c['post_title'] ?: '—'; ?></td>
                    <td>
                        <?php
                        $sc = ['pending'=>'#ff9f1c','approved'=>'#2ec4b6','spam'=>'#e63946'][$c['status']];
                        ?>
                        <span style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;background:<?php echo $sc; ?>22;color:<?php echo $sc; ?>;">
                            <?php echo ucfirst($c['status']); ?>
                        </span>
                    </td>
                    <td style="color:var(--text-muted);font-size:12px;"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:6px;">
                            <?php if ($c['status'] !== 'approved'): ?>
                            <a href="?approve=<?php echo $c['id']; ?>" class="btn" style="padding:5px 10px;background:rgba(46,196,182,.1);color:var(--success);border:none;" title="Approve"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                            <?php if ($c['status'] !== 'spam'): ?>
                            <a href="?spam=<?php echo $c['id']; ?>" class="btn" style="padding:5px 10px;background:rgba(255,159,28,.1);color:var(--warning);border:none;" title="Spam"><i class="fas fa-ban"></i></a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $c['id']; ?>" onclick="return confirm('Delete comment?')" class="btn" style="padding:5px 10px;background:rgba(230,57,70,.1);color:var(--danger);border:none;"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; if (empty($comments)): ?>
                <tr><td colspan="7" style="text-align:center;padding:60px;color:var(--text-muted);">No comments found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php include_once 'partials/footer.php'; ?>
