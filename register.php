<?php
/**
 * register.php
 * صفحة تسجيل مستخدم جديد
 */

session_start();

// إذا كان مسجل الدخول، نرجعه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// دمج ملفات الإعداد والكلاسات
require_once 'config.php'; // نستخدم config.php القديم لأن باقي المشروع يعتمد عليه
require_once 'classes/User.php';

$pdo = getConnection();
$userObj = new User($pdo);

$errors = [];
$name = $email = '';

// معالجة النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // التحقق البسيط
    if (empty($name))     $errors[] = 'الاسم مطلوب';
    if (empty($email))    $errors[] = 'البريد الإلكتروني مطلوب';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'صيغة البريد غير صحيحة';
    if (empty($password)) $errors[] = 'كلمة المرور مطلوبة';
    if (strlen($password) < 6) $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    if ($password !== $confirm_password) $errors[] = 'كلمتا المرور غير متطابقتين';

    // إذا لم تكن هناك أخطاء
    if (empty($errors)) {
        $userId = $userObj->register($name, $email, $password);

        if ($userId) {
            // تسجيل الدخول التلقائي بعد التسجيل
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            
            header('Location: index.php?welcome=1');
            exit;
        } else {
            $errors[] = 'البريد الإلكتروني مسجل مسبقاً، يرجى تسجيل الدخول.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - وصفاتي</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .auth-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: 0 4px 24px var(--shadow);
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
            <li><a href="login.php" class="btn-secondary btn-sm">تسجيل الدخول</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="auth-card">
        <h1 class="auth-title">✨ إنشاء حساب جديد</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name">الاسم الكامل</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required dir="ltr">
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required dir="ltr">
            </div>

            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" required dir="ltr">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                إنشاء الحساب
            </button>
        </form>

        <div class="auth-links">
            لديك حساب بالفعل؟ <a href="login.php">سجل دخولك هنا</a>
        </div>
    </div>
</div>

<script src="assets/script.js"></script>
</body>
</html>
