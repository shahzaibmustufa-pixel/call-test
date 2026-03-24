<?php
$page_title = 'Messages';
$active_page = 'messages';
require_once 'includes/auth_middleware.php';
require_permission('editor');
$db = getDB();

// Delete
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM messages WHERE id = ?")->execute([(int)$_GET['delete']]);
    redirect('messages.php', 'Message deleted.');
}
// Mark read
if (isset($_GET['read'])) {
    $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([(int)$_GET['read']]);
}

$view_msg = null;
if (isset($_GET['view'])) {
    $stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([(int)$_GET['view']]);
    $view_msg = $stmt->fetch();
    // Mark as read automatically
    if ($view_msg && !$view_msg['is_read']) {
        $db->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([$view_msg['id']]);
    }
}

$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
include_once 'partials/header.php';
?>
<div style="margin-bottom:30px;">
    <h1 style="font-size:24px;font-weight:800;">Messages</h1>
    <p style="color:var(--text-muted);">Contact form submissions from your visitors.</p>
</div>
<?php displayFlash(); ?>

<div style="display:grid;grid-template-columns:1fr <?php echo $view_msg ? '1.5fr' : ''; ?>;gap:30px;">
    <div class="card" style="padding:0;">
        <?php foreach ($messages as $msg): ?>
        <a href="?view=<?php echo $msg['id']; ?>" style="display:flex;gap:15px;padding:18px 20px;border-bottom:1px solid var(--border);transition:background .2s;<?php echo (!$msg['is_read'])?'background:rgba(67,97,238,.04);':''; ?><?php echo (isset($view_msg['id'])&&$view_msg['id']==$msg['id'])?'background:rgba(67,97,238,.1);':''; ?>">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($msg['name']); ?>&background=random" style="width:42px;height:42px;border-radius:50%;flex-shrink:0;">
            <div style="overflow:hidden;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:<?php echo (!$msg['is_read'])?'800':'600'; ?>;"><?php echo $msg['name']; ?></span>
                    <span style="font-size:11px;color:var(--text-muted);"><?php echo date('M d', strtotime($msg['created_at'])); ?></span>
                </div>
                <div style="font-size:13px;font-weight:600;color:var(--text-main);margin-top:2px;"><?php echo $msg['subject'] ?: '(No Subject)'; ?></div>
                <div style="font-size:12px;color:var(--text-muted);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;"><?php echo mb_strimwidth($msg['message'],0,60,'…'); ?></div>
            </div>
            <?php if (!$msg['is_read']): ?>
                <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:5px;"></div>
            <?php endif; ?>
        </a>
        <?php endforeach; if (empty($messages)): ?>
            <div style="text-align:center;padding:60px;color:var(--text-muted);">No messages yet.</div>
        <?php endif; ?>
    </div>

    <?php if ($view_msg): ?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:25px;">
            <div>
                <h2 style="font-size:20px;font-weight:700;"><?php echo $view_msg['subject'] ?: '(No Subject)'; ?></h2>
                <p style="color:var(--text-muted);margin-top:5px;">From: <strong><?php echo $view_msg['name']; ?></strong> &lt;<?php echo $view_msg['email']; ?>&gt;</p>
                <p style="color:var(--text-muted);font-size:12px;"><?php echo date('F d, Y \a\t g:i A', strtotime($view_msg['created_at'])); ?></p>
            </div>
            <a href="?delete=<?php echo $view_msg['id']; ?>" onclick="return confirm('Delete this message?')" class="btn" style="background:rgba(230,57,70,.1);color:var(--danger);border:none;"><i class="fas fa-trash"></i></a>
        </div>
        <div style="background:var(--bg-body);padding:25px;border-radius:12px;line-height:1.8;font-size:15px;"><?php echo nl2br($view_msg['message']); ?></div>
        <div style="margin-top:25px;">
            <a href="mailto:<?php echo $view_msg['email']; ?>?subject=Re: <?php echo urlencode($view_msg['subject']); ?>" class="btn btn-primary"><i class="fas fa-reply"></i> Reply via Email</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once 'partials/footer.php'; ?>
