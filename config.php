<?php
/**
 * config.php
 * ملف الإعدادات الرئيسي - معلومات الاتصال بقاعدة البيانات
 */

// --- إعدادات قاعدة البيانات ---
define('DB_HOST', 'localhost');       // عنوان السيرفر
define('DB_USER', 'root');            // اسم المستخدم (افتراضي في XAMPP)
define('DB_PASS', '');                // كلمة المرور (فارغة في XAMPP)
define('DB_NAME', 'recipe_manager');  // اسم قاعدة البيانات

// --- مجلد الصور ---
define('UPLOAD_DIR', __DIR__ . '/uploads/'); // المسار الكامل لمجلد الصور
define('UPLOAD_URL', 'uploads/');            // رابط مجلد الصور

/**
 * دالة إنشاء الاتصال بقاعدة البيانات باستخدام PDO
 * @return PDO كائن الاتصال
 */
function getConnection() {
    try {
        // إنشاء اتصال PDO مع تحديد UTF-8 لدعم العربية
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS
        );

        // إظهار الأخطاء كـ Exceptions لسهولة التشخيص
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // إرجاع البيانات كـ Array مترابطة (اسم العمود => القيمة)
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;

    } catch (PDOException $e) {
        // في حالة فشل الاتصال نوقف التنفيذ ونعرض رسالة واضحة
        die("
            <div style='color:red; padding:20px; font-family:Arial; direction:rtl;'>
                ❌ خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "
                <br><br>تأكد من:
                <ul>
                    <li>تشغيل MySQL في XAMPP</li>
                    <li>إنشاء قاعدة البيانات <b>recipe_manager</b></li>
                </ul>
            </div>
        ");
    }
}
