<?php
$page_title = 'Settings';
$active_page = 'settings';
require_once 'includes/auth_middleware.php';
require_permission('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $group = clean($_POST['group']);
    foreach ($_POST as $k => $v) {
        if ($k === 'group' || $k === 'csrf_token') continue;
        $db->prepare("INSERT INTO settings (setting_key, setting_value, group_name) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=?")
           ->execute([$k, clean($v), $group, clean($v)]);
    }
    // Handle logo / favicon upload
    foreach (['site_logo','site_favicon'] as $field) {
        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === 0) {
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $fn  = $field . '_' . time() . '.' . $ext;
            if (!is_dir('../uploads')) mkdir('../uploads', 0755, true);
            if (move_uploaded_file($_FILES[$field]['tmp_name'], '../uploads/' . $fn)) {
                $db->prepare("INSERT INTO settings (setting_key, setting_value, group_name) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=?")
                   ->execute([$field, $fn, 'general', $fn]);
            }
        }
    }
    redirect('settings.php?tab=' . $group, 'Settings saved!');
}

// Load all settings into associative array
$rows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$s = [];
foreach ($rows as $row) $s[$row['setting_key']] = $row['setting_value'];
$tab = $_GET['tab'] ?? 'general';

include_once 'partials/header.php';
?>
<div style="margin-bottom:30px;">
    <h1 style="font-size:24px;font-weight:800;">Settings</h1>
    <p style="color:var(--text-muted);">Manage general, SEO, social, and email server configuration.</p>
</div>
<?php displayFlash(); ?>

<!-- Tabs -->
<div style="display:flex;gap:5px;margin-bottom:25px;background:var(--bg-card);padding:6px;border-radius:14px;width:fit-content;border:1px solid var(--border);">
    <?php foreach (['general'=>'General','seo'=>'SEO','social'=>'Social Media','email'=>'Email / SMTP'] as $key=>$label): ?>
    <a href="?tab=<?php echo $key; ?>" style="padding:10px 22px;border-radius:10px;font-weight:600;font-size:14px;transition:all .2s;<?php echo $tab===$key?'background:var(--primary);color:white;':'color:var(--text-muted);'; ?>"><?php echo $label; ?></a>
    <?php endforeach; ?>
</div>

<form method="POST" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <input type="hidden" name="group" value="<?php echo $tab; ?>">

    <div class="card" style="max-width:750px;">
        <?php if ($tab === 'general'): ?>
            <h3 style="font-weight:700;margin-bottom:25px;">General Settings</h3>
            <div class="form-group">
                <label>Site Title</label>
                <input type="text" name="site_title" class="form-control" value="<?php echo $s['site_title'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Site Description</label>
                <textarea name="site_description" class="form-control" rows="3"><?php echo $s['site_description'] ?? ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Contact Email</label>
                <input type="email" name="contact_email" class="form-control" value="<?php echo $s['contact_email'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Site Logo</label>
                <?php if (!empty($s['site_logo'])): ?>
                    <div style="margin-bottom:10px;"><img src="../uploads/<?php echo $s['site_logo']; ?>" style="height:50px;border-radius:8px;"></div>
                <?php endif; ?>
                <input type="file" name="site_logo" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Favicon</label>
                <?php if (!empty($s['site_favicon'])): ?>
                    <div style="margin-bottom:10px;"><img src="../uploads/<?php echo $s['site_favicon']; ?>" style="height:32px;border-radius:4px;"></div>
                <?php endif; ?>
                <input type="file" name="site_favicon" class="form-control" accept="image/*">
            </div>

        <?php elseif ($tab === 'seo'): ?>
            <h3 style="font-weight:700;margin-bottom:25px;">SEO Settings</h3>
            <div class="form-group">
                <label>Global Meta Title Suffix</label>
                <input type="text" name="seo_title_suffix" class="form-control" value="<?php echo $s['seo_title_suffix'] ?? ''; ?>" placeholder="e.g.  | My Site">
            </div>
            <div class="form-group">
                <label>Global Meta Keywords</label>
                <input type="text" name="seo_keywords" class="form-control" value="<?php echo $s['seo_keywords'] ?? ''; ?>" placeholder="keyword1, keyword2">
            </div>
            <div class="form-group">
                <label>Google Analytics ID</label>
                <input type="text" name="ga_id" class="form-control" value="<?php echo $s['ga_id'] ?? ''; ?>" placeholder="G-XXXXXXXXXX">
            </div>
            <div class="form-group">
                <label>Google Search Console Verification</label>
                <input type="text" name="gsc_verify" class="form-control" value="<?php echo $s['gsc_verify'] ?? ''; ?>">
            </div>

        <?php elseif ($tab === 'social'): ?>
            <h3 style="font-weight:700;margin-bottom:25px;">Social Media Links</h3>
            <?php foreach (['social_facebook'=>'Facebook','social_twitter'=>'Twitter / X','social_instagram'=>'Instagram','social_youtube'=>'YouTube','social_linkedin'=>'LinkedIn'] as $k=>$l): ?>
            <div class="form-group">
                <label><?php echo $l; ?></label>
                <input type="url" name="<?php echo $k; ?>" class="form-control" value="<?php echo $s[$k] ?? ''; ?>" placeholder="https://">
            </div>
            <?php endforeach; ?>

        <?php elseif ($tab === 'email'): ?>
            <h3 style="font-weight:700;margin-bottom:25px;">Email / SMTP Settings</h3>
            <div class="form-group">
                <label>SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" value="<?php echo $s['smtp_host'] ?? ''; ?>" placeholder="smtp.example.com">
            </div>
            <div class="form-group">
                <label>SMTP Port</label>
                <input type="number" name="smtp_port" class="form-control" value="<?php echo $s['smtp_port'] ?? 587; ?>">
            </div>
            <div class="form-group">
                <label>SMTP Username</label>
                <input type="email" name="smtp_user" class="form-control" value="<?php echo $s['smtp_user'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>SMTP Password</label>
                <input type="password" name="smtp_pass" class="form-control" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Mail From Name</label>
                <input type="text" name="mail_from_name" class="form-control" value="<?php echo $s['mail_from_name'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Encryption</label>
                <select name="smtp_encryption" class="form-control">
                    <option value="tls" <?php echo ($s['smtp_encryption'] ?? 'tls')==='tls'?'selected':''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($s['smtp_encryption'] ?? '')==='ssl'?'selected':''; ?>>SSL</option>
                    <option value="none" <?php echo ($s['smtp_encryption'] ?? '')==='none'?'selected':''; ?>>None</option>
                </select>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="margin-top:10px;"><i class="fas fa-save"></i> Save Settings</button>
    </div>
</form>

<?php include_once 'partials/footer.php'; ?>
