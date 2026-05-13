<?php
/**
 * index.php
 * الصفحة الرئيسية - عرض كل الوصفات مع البحث والفلترة
 */

// نضم ملف الإعدادات والكلاسات
require_once 'config.php';
require_once 'classes/Recipe.php';

// نُنشئ اتصال قاعدة البيانات
$pdo = getConnection();

// نُنشئ كائن من كلاس Recipe
$recipeObj = new Recipe($pdo);

// --- معالجة البحث والفلترة ---
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// نجلب الوصفات حسب الحالة
if ($search !== '') {
    // البحث بكلمة مفتاحية
    $recipes = $recipeObj->search($search);
} else {
    // عرض كل الوصفات أو فلترة بتصنيف
    $recipes = $recipeObj->getAll($category);
}

// نجلب قائمة التصنيفات للقائمة المنسدلة
$categories = $recipeObj->getCategories();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>وصفات الطبخ - الصفحة الرئيسية</title>
    <meta name="description" content="موقع وصفات الطبخ - اكتشف أشهى الوصفات من حلويات وأكلات وعصائر">
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
            <li><a href="index.php" class="active">🏠 الرئيسية</a></li>
            <li><a href="add.php" class="btn-add">➕ أضف وصفة</a></li>
        </ul>
    </div>
</nav>

<!-- ===== Hero Section ===== -->
<div class="hero">
    <h1>🍽️ مرحباً بك في عالم الوصفات</h1>
    <p>اكتشف أشهى وصفات الطبخ، من الحلويات الشهية إلى الأكلات الشعبية والعصائر المنعشة</p>
</div>

<!-- ===== المحتوى الرئيسي ===== -->
<div class="container">

    <!-- شريط البحث والفلترة -->
    <div class="search-filter">
        <form method="GET" action="index.php">
            <!-- حقل البحث -->
            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="🔍 ابحث عن وصفة..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <!-- قائمة التصنيفات -->
            <select name="category" class="filter-select">
                <option value="">📋 كل التصنيفات</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"
                        <?= ($category === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- زر البحث -->
            <button type="submit" class="btn btn-primary">بحث</button>
            <!-- زر إعادة التعيين -->
            <?php if ($search !== '' || $category !== ''): ?>
                <a href="index.php" class="btn btn-secondary">إلغاء</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- عنوان القسم -->
    <h2 class="section-title">
        🍴
        <?php if ($search !== ''): ?>
            نتائج البحث عن "<?= htmlspecialchars($search) ?>" (<?= count($recipes) ?> نتيجة)
        <?php elseif ($category !== ''): ?>
            تصنيف: <?= htmlspecialchars($category) ?> (<?= count($recipes) ?> وصفة)
        <?php else: ?>
            كل الوصفات (<?= count($recipes) ?> وصفة)
        <?php endif; ?>
    </h2>

    <!-- شبكة الوصفات -->
    <?php if (empty($recipes)): ?>
        <!-- حالة عدم وجود وصفات -->
        <div class="empty-state">
            <div class="empty-icon">🥘</div>
            <h3>لا توجد وصفات بعد!</h3>
            <p style="margin-bottom:20px;">ابدأ بإضافة أول وصفة لك الآن</p>
            <a href="add.php" class="btn btn-primary">➕ أضف أول وصفة</a>
        </div>
    <?php else: ?>
        <div class="recipes-grid">
            <?php foreach ($recipes as $recipe): ?>
                <!-- بطاقة وصفة واحدة -->
                <div class="recipe-card">
                    <!-- صورة الوصفة -->
                    <a href="view.php?id=<?= $recipe['id'] ?>">
                        <?php
                        // نتحقق هل الصورة موجودة فعلاً
                        $imgPath = UPLOAD_URL . $recipe['image'];
                        $imgExists = ($recipe['image'] !== 'default.jpg' && file_exists(UPLOAD_DIR . $recipe['image']));
                        ?>
                        <?php if ($imgExists): ?>
                            <img
                                src="<?= htmlspecialchars($imgPath) ?>"
                                alt="<?= htmlspecialchars($recipe['title']) ?>"
                                class="recipe-card-img"
                            >
                        <?php else: ?>
                            <div class="recipe-card-img-placeholder">
                                <?php
                                // نختار إيموجي حسب التصنيف
                                $emojis = [
                                    'حلويات' => '🍰',
                                    'أكلات'  => '🍲',
                                    'عصائر'  => '🧃',
                                    'سلطات'  => '🥗',
                                    'شوربات' => '🥣',
                                ];
                                echo $emojis[$recipe['category']] ?? '🍽️';
                                ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <!-- تفاصيل البطاقة -->
                    <div class="recipe-card-body">
                        <!-- التصنيف -->
                        <span class="recipe-card-category">
                            <?= htmlspecialchars($recipe['category']) ?>
                        </span>

                        <!-- اسم الوصفة -->
                        <a href="view.php?id=<?= $recipe['id'] ?>">
                            <h3 class="recipe-card-title">
                                <?= htmlspecialchars($recipe['title']) ?>
                            </h3>
                        </a>

                        <!-- الفوتر: لايكات وتاريخ -->
                        <div class="recipe-card-footer">
                            <span class="recipe-likes">
                                ❤️ <?= $recipe['likes'] ?> إعجاب
                            </span>
                            <span class="recipe-card-date">
                                <?= date('d/m/Y', strtotime($recipe['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ===== الفوتر ===== -->
<footer class="footer">
    <p>صُنع بـ <span>❤️</span> لتعلم PHP و MySQL · <?= date('Y') ?></p>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
