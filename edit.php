<?php
/**
 * edit.php
 * صفحة تعديل وصفة موجودة
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';
require_once 'classes/Recipe.php';

$pdo = getConnection();
$recipeObj = new Recipe($pdo);

// نجلب الـ ID من الرابط
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// نجلب بيانات الوصفة
$recipe = $recipeObj->getById($id);

// إذا لم توجد الوصفة نرجع للصفحة الرئيسية
if (!$recipe) {
    header('Location: index.php');
    exit;
}

// قائمة التصنيفات
$categories = ['أكلات', 'حلويات', 'عصائر', 'سلطات', 'شوربات', 'مقبلات', 'مشروبات'];

$errors  = [];
$success = false;

// ===== معالجة النموذج عند الإرسال =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // جلب وتنظيف البيانات
    $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $ingredients  = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    // التحقق من البيانات
    if ($title === '')        $errors[] = 'اسم الوصفة مطلوب';
    if ($category === '')     $errors[] = 'التصنيف مطلوب';
    if ($ingredients === '')  $errors[] = 'المكونات مطلوبة';
    if ($instructions === '') $errors[] = 'طريقة التحضير مطلوبة';

    // معالجة الصورة الجديدة (إذا رفع المستخدم صورة)
    $imageName = $recipe['image']; // نحتفظ بالصورة القديمة كإعداد افتراضي

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['image'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowed)) {
            $errors[] = 'نوع الصورة غير مسموح. استخدم: jpg, png, gif, webp';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'حجم الصورة كبير جداً. الحد الأقصى 5MB';
        } else {
            // نحذف الصورة القديمة أولاً
            if ($recipe['image'] !== 'default.jpg') {
                $oldPath = UPLOAD_DIR . $recipe['image'];
                if (file_exists($oldPath)) unlink($oldPath);
            }

            // نرفع الصورة الجديدة
            $imageName = uniqid('recipe_') . '.' . $fileExt;
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $imageName)) {
                $errors[] = 'فشل رفع الصورة';
                $imageName = $recipe['image']; // نرجع للصورة القديمة
            }
        }
    }

    // إذا لا توجد أخطاء نحفظ التعديلات
    if (empty($errors)) {
        $recipeObj->update($id, $title, $category, $ingredients, $instructions, $imageName);
        // نوجّه لصفحة الوصفة بعد الحفظ
        header('Location: view.php?id=' . $id . '&edited=1');
        exit;
    }

    // إذا كانت هناك أخطاء نحتفظ بالقيم التي أدخلها المستخدم
    $recipe['title']        = $title;
    $recipe['category']     = $category;
    $recipe['ingredients']  = $ingredients;
    $recipe['instructions'] = $instructions;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الوصفة - وصفاتي</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo"><span class="emoji">🍳</span> وصفاتي</a>
        <ul class="navbar-links">
            <li><a href="index.php">🏠 الرئيسية</a></li>
            <li><span style="font-size: 0.9rem; margin-left: 10px; color: var(--text-light);">مرحباً، <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
            <li><a href="add.php" class="btn-add">➕ أضف وصفة</a></li>
            <li><a href="logout.php" class="btn-secondary btn-sm" style="border: none; color: var(--red-soft);">تسجيل الخروج</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <a href="view.php?id=<?= $id ?>" class="back-link">← رجوع للوصفة</a>

    <div class="form-card">
        <h1 class="form-title">✏️ تعديل: <?= htmlspecialchars($recipe['title']) ?></h1>

        <!-- رسائل الخطأ -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <div>
                    <strong>⚠️ يوجد أخطاء:</strong>
                    <ul style="margin-top:8px; padding-right:20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data">

            <div class="form-row">
                <!-- اسم الوصفة -->
                <div class="form-group">
                    <label for="title">🍽️ اسم الوصفة *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($recipe['title']) ?>"
                        required
                        maxlength="255"
                    >
                </div>

                <!-- التصنيف -->
                <div class="form-group">
                    <label for="category">📁 التصنيف *</label>
                    <select id="category" name="category" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= ($recipe['category'] === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- المكونات -->
            <div class="form-group">
                <label for="ingredients">🥕 المكونات *</label>
                <textarea id="ingredients" name="ingredients" required rows="6">
<?= htmlspecialchars($recipe['ingredients']) ?></textarea>
            </div>

            <!-- طريقة التحضير -->
            <div class="form-group">
                <label for="instructions">👨‍🍳 طريقة التحضير *</label>
                <textarea id="instructions" name="instructions" required rows="7">
<?= htmlspecialchars($recipe['instructions']) ?></textarea>
            </div>

            <!-- الصورة -->
            <div class="form-group">
                <label for="image">📸 تغيير الصورة (اختياري)</label>
                <!-- عرض الصورة الحالية -->
                <?php
                $currentImgExists = ($recipe['image'] !== 'default.jpg' && file_exists(UPLOAD_DIR . $recipe['image']));
                ?>
                <?php if ($currentImgExists): ?>
                    <div class="current-image-preview">
                        <img src="<?= htmlspecialchars(UPLOAD_URL . $recipe['image']) ?>"
                             alt="الصورة الحالية">
                    </div>
                    <p class="form-hint">⬆️ هذه هي الصورة الحالية. ارفع صورة جديدة لتغييرها</p>
                <?php else: ?>
                    <p class="form-hint">لا توجد صورة حالية</p>
                <?php endif; ?>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/*"
                    onchange="previewImage(this)"
                    style="margin-top:10px;"
                >
                <img id="image-preview"
                     style="display:none; margin-top:12px; max-width:220px; border-radius:10px; border:2px solid var(--border);"
                     alt="معاينة الصورة الجديدة">
            </div>

            <!-- أزرار -->
            <div style="display:flex; gap:12px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
                <a href="view.php?id=<?= $id ?>" class="btn btn-secondary">إلغاء</a>
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
