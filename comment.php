<?php
/**
 * comment.php
 * معالجة إضافة تعليق جديد
 * يستقبل POST ويعيد التوجيه لصفحة الوصفة
 */

require_once 'config.php';
require_once 'classes/Comment.php';

$pdo = getConnection();
$commentObj = new Comment($pdo);

// نتحقق أن الطلب POST وليس GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// جلب وتنظيف البيانات
$recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
$author    = trim($_POST['author'] ?? '');
$content   = trim($_POST['content'] ?? '');

// التحقق البسيط من البيانات
if ($recipe_id <= 0 || $author === '' || $content === '') {
    // نرجع للوصفة مع رسالة خطأ
    header('Location: view.php?id=' . $recipe_id . '&error=empty');
    exit;
}

// نحذّ القيم من أي محتوى ضار (أمان بسيط)
$author  = htmlspecialchars($author, ENT_QUOTES, 'UTF-8');

// نضيف التعليق لقاعدة البيانات
$commentObj->create($recipe_id, $author, $content);

// نوجّه المستخدم للوصفة بعد إضافة التعليق
header('Location: view.php?id=' . $recipe_id . '&commented=1');
exit;
