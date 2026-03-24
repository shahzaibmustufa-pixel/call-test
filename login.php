<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            redirect('index.php', 'Welcome back, ' . $user['username'] . '!');
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #4361ee 0%, #4cc9f0 100%);
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            background: var(--bg-card);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            text-align: center;
        }
        .login-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 10px;
        }
        .login-header p {
            color: var(--text-muted);
            margin-bottom: 30px;
        }
        .form-group {
            text-align: left;
        }
        .form-control {
            height: 50px;
            padding: 0 20px;
            font-size: 16px;
        }
        .btn-login {
            width: 100%;
            height: 50px;
            font-size: 16px;
            margin-top: 20px;
            border-radius: 12px;
        }
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 50px;
            background: white;
            color: #444;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin-top: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .google-btn:hover { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Enter your credentials to access the admin panel</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@admin.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div style="text-align: right; margin-bottom: 20px;">
                <a href="#" style="color: var(--primary); font-size: 14px; font-weight: 500;">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-login">Sign In</button>
        </form>

        <div style="margin: 25px 0; color: #ccc; font-size: 14px;">OR</div>

        <button class="google-btn">
            <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google" style="width: 20px;">
            Continue with Google
        </button>
    </div>
</body>
</html>