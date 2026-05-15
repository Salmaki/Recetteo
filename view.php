<?php

require_once 'config.php';
require_once 'classes/Recipe.php';
require_once 'classes/Comment.php';

$pdo = getConnection();

// نُنشئ كائنات الكلاسات
$recipeObj  = new Recipe($pdo);
$commentObj = new Comment($pdo);

// نجلب الـ ID من الرابط ونتحقق أنه رقم
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// نجلب بيانات الوصفة
$recipe = $recipeObj->getById($id);

// إذا لم توجد الوصفة نعيد التوجيه للصفحة الرئيسية
if (!$recipe) {
    header('Location: index.php');
    exit;
}

// نجلب تعليقات هذه الوصفة
$comments = $commentObj->getByRecipeId($id);

// --- رسالة النجاح إذا تمت إضافة تعليق ---
$successMsg = isset($_GET['commented']) ? 'تمت إضافة تعليقك بنجاح! 🎉' : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?> - وصفاتي</title>
    <meta name="description" content="وصفة <?= htmlspecialchars($recipe['title']) ?> - المكونات وطريقة التحضير">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ===== شريط التنقل ===== -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">
            <span class="emoji">🍳</span>
            وصفاتي
        </a>
        <ul class="navbar-links">
            <li><a href="index.php">🏠 الرئيسية</a></li>
            <li><a href="add.php" class="btn-add">➕ أضف وصفة</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <!-- رابط الرجوع -->
    <a href="index.php" class="back-link">← رجوع إلى كل الوصفات</a>

    <!-- رسالة نجاح التعليق -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success">✅ <?= $successMsg ?></div>
    <?php endif; ?>

    <!-- ===== تفاصيل الوصفة ===== -->
    <div class="recipe-detail">

        <!-- صورة الوصفة -->
        <?php
        $imgExists = ($recipe['image'] !== 'default.jpg' && file_exists(UPLOAD_DIR . $recipe['image']));
        $emojis = ['حلويات'=>'🍰','أكلات'=>'🍲','عصائر'=>'🧃','سلطات'=>'🥗','شوربات'=>'🥣'];
        ?>
        <?php if ($imgExists): ?>
            <img
                src="<?= htmlspecialchars(UPLOAD_URL . $recipe['image']) ?>"
                alt="<?= htmlspecialchars($recipe['title']) ?>"
                class="recipe-detail-img"
            >
        <?php else: ?>
            <div class="recipe-detail-img-placeholder">
                <?= $emojis[$recipe['category']] ?? '🍽️' ?>
            </div>
        <?php endif; ?>

        <div class="recipe-detail-body">

            <!-- رأس الوصفة: الاسم وأزرار التعديل/الحذف -->
            <div class="recipe-detail-header">
                <h1 class="recipe-detail-title">
                    <?= htmlspecialchars($recipe['title']) ?>
                </h1>
                <!-- أزرار التعديل والحذف -->
                <div class="recipe-actions">
                    <a href="edit.php?id=<?= $recipe['id'] ?>" class="btn btn-edit btn-sm">
                        ✏️ تعديل
                    </a>
                    <a
                        href="delete.php?id=<?= $recipe['id'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirmDelete('<?= addslashes($recipe['title']) ?>')"
                    >
                        🗑️ حذف
                    </a>
                </div>
            </div>

            <!-- شارات المعلومات -->
            <div class="recipe-detail-meta">
                <span class="meta-badge category">
                    📁 <?= htmlspecialchars($recipe['category']) ?>
                </span>
                <span class="meta-badge date">
                    📅 <?= date('d/m/Y', strtotime($recipe['created_at'])) ?>
                </span>
                <span class="meta-badge likes">
                    ❤️ <span id="like-count"><?= $recipe['likes'] ?></span> إعجاب
                </span>
            </div>

            <!-- ===== المكونات ===== -->
            <div class="recipe-section">
                <h3>🥕 المكونات</h3>
                <div class="recipe-section-content">
                    <?= nl2br(htmlspecialchars($recipe['ingredients'])) ?>
                </div>
            </div>

            <!-- ===== طريقة التحضير ===== -->
            <div class="recipe-section">
                <h3>👨‍🍳 طريقة التحضير</h3>
                <div class="recipe-section-content">
                    <?= nl2br(htmlspecialchars($recipe['instructions'])) ?>
                </div>
            </div>

            <!-- ===== زر اللايك ===== -->
            <div style="margin-top: 28px;">
                <button
                    id="like-btn"
                    class="like-btn"
                    onclick="likeRecipe(<?= $recipe['id'] ?>)"
                >
                    ❤️ أعجبني هذا الطبق
                    <span id="like-count-btn">(<?= $recipe['likes'] ?>)</span>
                </button>
                <p style="margin-top:8px; font-size:0.82rem; color:#888;">
                    * يمكنك الإعجاب بالوصفة مرة واحدة
                </p>
            </div>

        </div><!-- .recipe-detail-body -->
    </div><!-- .recipe-detail -->

    <!-- ===== قسم التعليقات ===== -->
    <div class="comments-section">
        <h2 class="section-title">
            💬 التعليقات (<?= count($comments) ?>)
        </h2>

        <!-- عرض التعليقات -->
        <?php if (empty($comments)): ?>
            <div class="empty-state" style="padding:30px 20px;">
                <div class="empty-icon" style="font-size:3rem;">💬</div>
                <p>لا توجد تعليقات بعد. كن أول من يعلّق!</p>
            </div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card">
                    <div class="comment-author">
                        👤 <?= htmlspecialchars($comment['author']) ?>
                    </div>
                    <div class="comment-date">
                        🕐 <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                    </div>
                    <p class="comment-text">
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ===== نموذج إضافة تعليق ===== -->
        <div style="margin-top: 32px; border-top: 2px solid var(--border); padding-top: 28px;">
            <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:18px; color:var(--primary);">
                ✍️ أضف تعليقك
            </h3>
            <form method="POST" action="comment.php">
                <!-- معرّف الوصفة (مخفي) -->
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">

                <div class="form-group">
                    <label for="author">اسمك</label>
                    <input
                        type="text"
                        id="author"
                        name="author"
                        placeholder="أدخل اسمك..."
                        required
                        maxlength="100"
                    >
                </div>

                <div class="form-group">
                    <label for="content">تعليقك</label>
                    <textarea
                        id="content"
                        name="content"
                        placeholder="اكتب تعليقك هنا..."
                        required
                        rows="4"
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    💬 إرسال التعليق
                </button>
            </form>
        </div>
    </div>

</div><!-- .container -->

<!-- ===== الفوتر ===== -->
<footer class="footer">
    <p>صُنع بـ <span>❤️</span> لتعلم PHP و MySQL · <?= date('Y') ?></p>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
