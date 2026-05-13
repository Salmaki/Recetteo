<?php
/**
 * delete.php
 * صفحة تأكيد وتنفيذ حذف الوصفة
 */

require_once 'config.php';
require_once 'classes/Recipe.php';

$pdo = getConnection();
$recipeObj = new Recipe($pdo);

// نجلب الـ ID من الرابط
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// نجلب بيانات الوصفة للتأكيد
$recipe = $recipeObj->getById($id);

// إذا لم توجد الوصفة نرجع للصفحة الرئيسية
if (!$recipe) {
    header('Location: index.php');
    exit;
}

// ===== تنفيذ الحذف عند الضغط على زر التأكيد =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // نستدعي دالة الحذف التي تحذف الوصفة + صورتها + تعليقاتها
    $recipeObj->delete($id);

    // نوجّه للصفحة الرئيسية بعد الحذف
    header('Location: index.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حذف الوصفة - وصفاتي</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* تصميم خاص بصفحة الحذف */
        .delete-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: 0 4px 24px var(--shadow);
            padding: 40px;
            max-width: 560px;
            margin: 0 auto;
            text-align: center;
        }
        .delete-icon {
            font-size: 4rem;
            margin-bottom: 16px;
        }
        .delete-card h1 {
            font-size: 1.6rem;
            color: var(--red-soft);
            margin-bottom: 12px;
        }
        .delete-card p {
            color: var(--text-light);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .recipe-name-preview {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 10px;
            padding: 14px 20px;
            margin: 20px 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
        }
        .warning-text {
            background: #fff8f0;
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--primary-dark);
            font-size: 0.88rem;
            margin-bottom: 28px;
        }
        .delete-buttons {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo"><span class="emoji">🍳</span> وصفاتي</a>
        <ul class="navbar-links">
            <li><a href="index.php">🏠 الرئيسية</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <a href="view.php?id=<?= $id ?>" class="back-link">← رجوع للوصفة</a>

    <div class="delete-card">

        <!-- أيقونة التحذير -->
        <div class="delete-icon">🗑️</div>

        <h1>تأكيد الحذف</h1>

        <p>أنت على وشك حذف الوصفة التالية:</p>

        <!-- اسم الوصفة -->
        <div class="recipe-name-preview">
            🍽️ <?= htmlspecialchars($recipe['title']) ?>
            <br>
            <small style="font-weight:400; color:var(--text-light);">
                <?= htmlspecialchars($recipe['category']) ?>
            </small>
        </div>

        <!-- تحذير -->
        <div class="warning-text">
            ⚠️ سيتم حذف الوصفة وصورتها وجميع تعليقاتها (<?= $recipe['likes'] ?> إعجاب).
            <br>لا يمكن التراجع عن هذا الإجراء!
        </div>

        <!-- نموذج الحذف -->
        <form method="POST" action="delete.php?id=<?= $id ?>">
            <div class="delete-buttons">
                <!-- زر تأكيد الحذف -->
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    🗑️ نعم، احذف الوصفة
                </button>
                <!-- زر الإلغاء -->
                <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">
                    ❌ إلغاء
                </a>
            </div>
        </form>

    </div>
</div>

<footer class="footer">
    <p>صُنع بـ <span>❤️</span> لتعلم PHP و MySQL · <?= date('Y') ?></p>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
