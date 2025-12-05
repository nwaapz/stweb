<?php
/**
 * Admin Login Page
 * صفحه ورود مدیریت
 */

require_once __DIR__ . '/../includes/functions.php';

$error = '';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_username'] = $user['username'];
            
            // Update last login
            $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت | استارتک</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .login-header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        }
        .input-group-text {
            border-radius: 0 10px 10px 0;
            border: 2px solid #e0e0e0;
            border-left: none;
            background: #f8f9fa;
        }
        .input-group .form-control {
            border-radius: 10px 0 0 10px;
            border-left: none;
        }
        .default-login {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1><i class="bi bi-gear-fill"></i> پنل مدیریت</h1>
            <p>سیستم مدیریت محتوا استارتک</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">نام کاربری</label>
                    <div class="input-group">
                        <input type="text" name="username" class="form-control" placeholder="نام کاربری را وارد کنید" required>
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">رمز عبور</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" placeholder="رمز عبور را وارد کنید" required>
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger btn-login">
                    <i class="bi bi-box-arrow-in-left"></i> ورود به سیستم
                </button>
            </form>
            
            <div class="default-login text-center text-muted">
                <strong>اطلاعات ورود پیش‌فرض:</strong><br>
                نام کاربری: <code>admin</code> | رمز عبور: <code>admin123</code>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
