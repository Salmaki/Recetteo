<?php
/**
 * login.php
 * صفحة تسجيل الدخول
 */

session_start();

// إذا كان مسجل الدخول، نرجعه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';
require_once 'classes/User.php';

$pdo = getConnection();
$userObj = new User($pdo);

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email))    $errors[] = 'البريد الإلكتروني مطلوب';
    if (empty($password)) $errors[] = 'كلمة المرور مطلوبة';

    if (empty($errors)) {
        $user = $userObj->login($email, $password);

        if ($user) {
            // تسجيل الدخول ناجح
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            header('Location: index.php?welcome=1');
            exit;
        } else {
            $errors[] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - وصفاتي</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .auth-card {
            background: var(--white);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 8px 32px var(--shadow);
            padding: 40px;
            max-width: 450px;
            margin: 40px auto;
        }
        .auth-title {
            text-align: center;
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 24px;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
            font-size: 0.95rem;
        }
        .auth-links a {
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo"><span class="emoji">🍳</span> وصفاتي</a>
        <ul class="navbar-links">
            <li><a href="index.php">🏠 الرئيسية</a></li>
            <li><a href="register.php" class="btn-secondary btn-sm">إنشاء حساب</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="auth-card">
        <h1 class="auth-title">👋 مرحباً بك مجدداً</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required dir="ltr">
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required dir="ltr">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                تسجيل الدخول
            </button>
        </form>

        <div class="auth-links">
            ليس لديك حساب؟ <a href="register.php">أنشئ حساباً جديداً</a>
        </div>
    </div>
</div>

<script src="assets/script.js"></script>
</body>
</html>
