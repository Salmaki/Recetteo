-- ============================================
-- database.sql
-- ملف إنشاء قاعدة البيانات والجداول
-- شغّل هذا الملف في phpMyAdmin أو MySQL
-- ============================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS recipe_manager
    CHARACTER SET utf8
    COLLATE utf8_unicode_ci;

-- استخدام قاعدة البيانات
USE recipe_manager;

-- ============================================
-- جدول الوصفات (recipes)
-- ============================================
CREATE TABLE IF NOT EXISTS recipes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL          COMMENT 'اسم الوصفة',
    category     VARCHAR(100) NOT NULL          COMMENT 'التصنيف (حلويات، أكلات، عصائر...)',
    ingredients  TEXT         NOT NULL          COMMENT 'المكونات',
    instructions TEXT         NOT NULL          COMMENT 'طريقة التحضير',
    image        VARCHAR(255) DEFAULT 'default.jpg' COMMENT 'اسم ملف الصورة',
    likes        INT          DEFAULT 0         COMMENT 'عدد الإعجابات',
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإضافة'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- جدول التعليقات (comments)
-- ============================================
CREATE TABLE IF NOT EXISTS comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id  INT          NOT NULL COMMENT 'معرّف الوصفة المرتبطة',
    author     VARCHAR(100) NOT NULL COMMENT 'اسم صاحب التعليق',
    content    TEXT         NOT NULL COMMENT 'نص التعليق',
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ التعليق',
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ============================================
-- بيانات تجريبية لاختبار الموقع
-- ============================================
INSERT INTO recipes (title, category, ingredients, instructions, image, likes) VALUES
(
    'كيكة الشوكولاتة الهشة',
    'حلويات',
    '- 2 كوب دقيق
- 1 كوب سكر
- 1/2 كوب كاكاو
- 3 بيضات
- 1 كوب حليب
- 1/2 كوب زيت نباتي
- 1 ملعقة صغيرة بيكنج باودر',
    '1. سخّن الفرن على 180 درجة مئوية
2. اخلط المواد الجافة (دقيق، سكر، كاكاو، بيكنج باودر)
3. في وعاء آخر اخفق البيض مع الحليب والزيت
4. ادمج الخليطين معاً وحرّك حتى يتجانس العجين
5. صبّ في قالب مدهون واخبز 35 دقيقة',
    'default.jpg',
    12
),
(
    'شوربة العدس الدافئة',
    'أكلات',
    '- 1 كوب عدس أحمر
- 1 بصلة كبيرة
- 2 فص ثوم
- 1 جزرة
- ملح وكمون وفلفل أسود
- 2 ملعقة زيت زيتون
- 4 أكواب ماء',
    '1. اغسل العدس جيداً بالماء
2. قلّي البصل والثوم في الزيت حتى يذهب الفطر
3. أضف الجزرة المقطعة وحرّك دقيقتين
4. أضف العدس والماء والتوابل
5. اطبخ على نار هادئة 20 دقيقة
6. اخلط بالخلاط حتى تصبح ناعمة',
    'default.jpg',
    8
),
(
    'عصير المانجو المنعش',
    'عصائر',
    '- 2 حبة مانجو ناضجة
- عصير حبة ليمون
- 2 ملعقة سكر
- 1 كوب ماء بارد
- ثلج حسب الرغبة',
    '1. قشّر المانجو وقطّعها إلى مكعبات
2. ضعها في الخلاط مع الليمون والسكر والماء
3. اخلط جيداً حتى تصبح ناعمة
4. صبّ في كأس مع الثلج وقدّم فوراً',
    'default.jpg',
    15
);

INSERT INTO comments (recipe_id, author, content) VALUES
(1, 'سارة', 'جربتها وطلعت رائعة! شكراً على الوصفة 😍'),
(1, 'أحمد', 'أفضل كيكة شوكولاتة جربتها في حياتي'),
(3, 'ريم', 'عصير منعش جداً، أضفت نعناعاً وكان لذيذاً 🌿');
