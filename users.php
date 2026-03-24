<?php
$page_title = 'User Management';
$active_page = 'users';
require_once 'includes/auth_middleware.php';
require_permission('admin');          // Only admins may manage users
$db = getDB();

$errors = [];

// --- Delete ---
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id === (int)$_SESSION['user_id']) {
        redirect('users.php', 'You cannot delete yourself!', 'danger');
    }
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
    redirect('users.php', 'User deleted.');
}

// --- Toggle status ---
if (isset($_GET['toggle'])) {
    $tog_id = (int)$_GET['toggle'];
    $cur = $db->prepare("SELECT status FROM users WHERE id = ?");
    $cur->execute([$tog_id]);
    $row = $cur->fetch();
    if ($row) {
        $new = $row['status'] === 'active' ? 'suspended' : 'active';
        $db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$new, $tog_id]);
    }
    redirect('users.php', 'User status updated.');
}

// --- Create / Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $edit_id   = (int)($_POST['user_id'] ?? 0);
    $username  = clean($_POST['username']);
    $email     = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role      = in_array($_POST['role'], ['admin','editor','author']) ? $_POST['role'] : 'author';
    $password  = $_POST['password'] ?? '';

    if (empty($username)) $errors[] = 'Username is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';

    if (empty($errors)) {
        if ($edit_id) {
            $sql = "UPDATE users SET username=?, email=?, role=?" . (!empty($password) ? ", password=?" : "") . " WHERE id=?";
            $params = [$username, $email, $role];
            if (!empty($password)) $params[] = password_hash($password, PASSWORD_BCRYPT);
            $params[] = $edit_id;
            $db->prepare($sql)->execute($params);
            redirect('users.php', 'User updated successfully!');
        } else {
            if (empty($password)) { $errors[] = 'Password is required for new users.'; }
            else {
                $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,?)")
                   ->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), $role]);
                // Notify admin
                $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?,?,?)")
                   ->execute([$_SESSION['user_id'], 'new_user', "New user registered: $username"]);
                redirect('users.php', 'User created successfully!');
            }
        }
    }
}

// Fetch list
$search = clean($_GET['s'] ?? '');
$role_filter = $_GET['role'] ?? '';
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (username LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($role_filter) { $sql .= " AND role = ?"; $params[] = $role_filter; }
$sql .= " ORDER BY created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$users = $stmt->fetchAll();

$edit_user = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM users WHERE id = ?");
    $s->execute([(int)$_GET['edit']]);
    $edit_user = $s->fetch();
}

include_once 'partials/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1 style="font-size:24px;font-weight:800;">User Management</h1>
        <p style="color:var(--text-muted);">Add, edit, suspend, and assign roles to users.</p>
    </div>
    <button onclick="document.getElementById('user-modal').style.display='flex'" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add User
    </button>
</div>
<?php displayFlash(); ?>

<!-- Filters -->
<div class="card" style="padding:20px;margin-bottom:25px;">
    <form method="GET" style="display:flex;gap:15px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Search</label>
            <input type="text" name="s" class="form-control" value="<?php echo $search; ?>" placeholder="Search by name or email...">
        </div>
        <div style="width:150px;">
            <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Role</label>
            <select name="role" class="form-control">
                <option value="">All Roles</option>
                <option value="admin" <?php echo $role_filter==='admin'?'selected':''; ?>>Admin</option>
                <option value="editor" <?php echo $role_filter==='editor'?'selected':''; ?>>Editor</option>
                <option value="author" <?php echo $role_filter==='author'?'selected':''; ?>>Author</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="users.php" class="btn" style="background:var(--bg-body);border:1px solid var(--border);">Reset</a>
    </form>
</div>

<!-- User Table -->
<div class="card">
    <div class="table-container">
        <table>
            <thead><tr>
                <th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th style="text-align:right;">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div style="display:flex;gap:12px;align-items:center;">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['username']); ?>&background=random" style="width:38px;height:38px;border-radius:50%;">
                        <span style="font-weight:600;"><?php echo $u['username']; ?></span>
                    </div>
                </td>
                <td style="color:var(--text-muted);"><?php echo $u['email']; ?></td>
                <td>
                    <?php
                    $role_colors = ['admin'=>'#4361ee','editor'=>'#2ec4b6','author'=>'#ff9f1c'];
                    $c = $role_colors[$u['role']] ?? '#808191';
                    ?>
                    <span style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;background:<?php echo $c; ?>22;color:<?php echo $c; ?>;">
                        <?php echo ucfirst($u['role']); ?>
                    </span>
                </td>
                <td>
                    <span style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;<?php echo $u['status']==='active'?'background:rgba(46,196,182,.1);color:#2ec4b6;':'background:rgba(230,57,70,.1);color:#e63946;'; ?>">
                        <?php echo ucfirst($u['status']); ?>
                    </span>
                </td>
                <td style="color:var(--text-muted);font-size:13px;"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td style="text-align:right;">
                    <div style="display:flex;justify-content:flex-end;gap:8px;">
                        <a href="?edit=<?php echo $u['id']; ?>" onclick="document.getElementById('user-modal').style.display='flex'" class="btn" style="padding:5px 10px;background:rgba(67,97,238,.1);color:var(--primary);border:none;"><i class="fas fa-edit"></i></a>
                        <a href="?toggle=<?php echo $u['id']; ?>" class="btn" style="padding:5px 10px;background:rgba(255,159,28,.1);color:var(--warning);border:none;" title="Toggle status"><i class="fas fa-user-slash"></i></a>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete=<?php echo $u['id']; ?>" onclick="return confirm('Delete this user?')" class="btn" style="padding:5px 10px;background:rgba(230,57,70,.1);color:var(--danger);border:none;"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; if (empty($users)): ?>
            <tr><td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted);">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="user-modal" style="display:<?php echo ($edit_user || !empty($errors)) ? 'flex' : 'none'; ?>;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;">
    <div class="card" style="width:480px;max-height:90vh;overflow-y:auto;position:relative;">
        <button onclick="document.getElementById('user-modal').style.display='none'" style="position:absolute;top:20px;right:20px;background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-muted);">&times;</button>
        <h3 style="font-weight:700;margin-bottom:20px;"><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h3>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>
        <form method="POST">
            <?php csrf_field(); ?>
            <?php if ($edit_user): ?>
                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $edit_user['username'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $edit_user['email'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Password <?php echo $edit_user ? '(leave blank to keep current)' : ''; ?></label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" <?php echo $edit_user ? '' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="author" <?php echo (isset($edit_user['role']) && $edit_user['role']==='author')?'selected':''; ?>>Author</option>
                    <option value="editor" <?php echo (isset($edit_user['role']) && $edit_user['role']==='editor')?'selected':''; ?>>Editor</option>
                    <option value="admin"  <?php echo (isset($edit_user['role']) && $edit_user['role']==='admin')?'selected':''; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo $edit_user ? 'Update User' : 'Create User'; ?></button>
        </form>
    </div>
</div>

<?php include_once 'partials/footer.php'; ?>
