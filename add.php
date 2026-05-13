<?php
/**
 * add.php
 * صفحة إضافة وصفة جديدة
 */

session_start();

// يجب أن يكون المستخدم مسجل الدخول لإضافة وصفة
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';
require_once 'classes/Recipe.php';

$pdo = getConnection();
$recipeObj = new Recipe($pdo);

// قائمة التصنيفات المتاحة
$categories = ['أكلات', 'حلويات', 'عصائر', 'سلطات', 'شوربات', 'مقبلات', 'مشروبات'];

$errors = [];     // لتجميع رسائل الخطأ
$success = false; // هل تمت الإضافة بنجاح؟

// ===== معالجة النموذج عند الإرسال =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- جلب وتنظيف البيانات المدخلة ---
    $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $ingredients  = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    // --- التحقق من البيانات ---
    if ($title === '')        $errors[] = 'اسم الوصفة مطلوب';
    if ($category === '')     $errors[] = 'التصنيف مطلوب';
    if ($ingredients === '')  $errors[] = 'المكونات مطلوبة';
    if ($instructions === '') $errors[] = 'طريقة التحضير مطلوبة';

    // --- معالجة رفع الصورة ---
    $imageName = 'default.jpg'; // الصورة الافتراضية

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['image'];
        $fileSize = $file['size'];
        $fileExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // أنواع الملفات المسموحة فقط
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowedExt)) {
            $errors[] = 'نوع الصورة غير مسموح. استخدم: jpg, png, gif, webp';
        } elseif ($fileSize > 5 * 1024 * 1024) {
            // الحد الأقصى 5 ميجابايت
            $errors[] = 'حجم الصورة كبير جداً. الحد الأقصى 5MB';
        } else {
            // نُنشئ اسماً فريداً للصورة لتجنب التعارض
            $imageName = uniqid('recipe_') . '.' . $fileExt;

            // نتأكد من وجود مجلد الصور
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }

            // نرفع الصورة إلى مجلد uploads
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $imageName)) {
                $errors[] = 'فشل رفع الصورة، تحقق من صلاحيات المجلد';
                $imageName = 'default.jpg';
            }
        }
    }

    // --- إذا لا توجد أخطاء، نحفظ الوصفة ---
    if (empty($errors)) {
        $newId = $recipeObj->create($title, $category, $ingredients, $instructions, $imageName);
        // نوجّه المستخدم لصفحة الوصفة الجديدة
        header('Location: view.php?id=' . $newId . '&added=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة وصفة جديدة - وصفاتي</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ===== شريط التنقل ===== -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-logo">
            <span class="emoji">🍳</span> وصفاتي
        </a>
        <ul class="navbar-links">
            <li><a href="index.php">🏠 الرئيسية</a></li>
            <li><span style="font-size: 0.9rem; margin-left: 10px; color: var(--text-light);">مرحباً، <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
            <li><a href="add.php" class="active btn-add">➕ أضف وصفة</a></li>
            <li><a href="logout.php" class="btn-secondary btn-sm" style="border: none; color: var(--red-soft);">تسجيل الخروج</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <a href="index.php" class="back-link">← رجوع</a>

    <!-- ===== نموذج الإضافة ===== -->
    <div class="form-card">
        <h1 class="form-title">➕ إضافة وصفة جديدة</h1>

        <!-- عرض رسائل الخطأ -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <div>
                    <strong>⚠️ يوجد بعض الأخطاء:</strong>
                    <ul style="margin-top:8px; padding-right:20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="add.php" enctype="multipart/form-data">

            <!-- اسم الوصفة والتصنيف في صف واحد -->
            <div class="form-row">
                <div class="form-group">
                    <label for="title">🍽️ اسم الوصفة *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="مثال: كيكة الشوكولاتة"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        required
                        maxlength="255"
                    >
                </div>

                <div class="form-group">
                    <label for="category">📁 التصنيف *</label>
                    <select id="category" name="category" required>
                        <option value="">-- اختر التصنيف --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- المكونات -->
            <div class="form-group">
                <label for="ingredients">🥕 المكونات *</label>
                <textarea
                    id="ingredients"
                    name="ingredients"
                    placeholder="اكتب كل مكوّن في سطر منفصل...
مثال:
- 2 كوب دقيق
- 1 كوب سكر
- 3 بيضات"
                    required
                    rows="6"
                ><?= htmlspecialchars($_POST['ingredients'] ?? '') ?></textarea>
                <p class="form-hint">💡 ضع كل مكوّن في سطر منفصل لسهولة القراءة</p>
            </div>

            <!-- طريقة التحضير -->
            <div class="form-group">
                <label for="instructions">👨‍🍳 طريقة التحضير *</label>
                <textarea
                    id="instructions"
                    name="instructions"
                    placeholder="اكتب خطوات التحضير بالترتيب...
مثال:
1. سخّن الفرن على 180 درجة
2. اخلط المكونات الجافة
3. أضف البيض والحليب..."
                    required
                    rows="7"
                ><?= htmlspecialchars($_POST['instructions'] ?? '') ?></textarea>
            </div>

            <!-- رفع الصورة -->
            <div class="form-group">
                <label for="image">📸 صورة الوصفة (اختياري)</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/*"
                    onchange="previewImage(this)"
                >
                <p class="form-hint">الأنواع المقبولة: jpg, png, gif, webp · الحد الأقصى: 5MB</p>
                <!-- معاينة الصورة قبل الرفع -->
                <img
                    id="image-preview"
                    style="display:none; margin-top:12px; max-width:220px; border-radius:10px; border:2px solid var(--border);"
                    alt="معاينة الصورة"
                >
            </div>

            <!-- أزرار الإرسال -->
            <div style="display:flex; gap:12px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">
                    💾 حفظ الوصفة
                </button>
                <a href="index.php" class="btn btn-secondary">إلغاء</a>
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
