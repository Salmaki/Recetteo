<?php
/**
 * like.php
 * معالجة طلب الإعجاب (Like) عبر AJAX
 * يستقبل POST ويرجع JSON
 */

require_once 'config.php';
require_once 'classes/Recipe.php';

$pdo = getConnection();
$recipeObj = new Recipe($pdo);

// نخبر المتصفح أن الرد سيكون JSON
header('Content-Type: application/json');

// نتحقق أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة غير صحيحة']);
    exit;
}

// نجلب ID الوصفة
$recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;

if ($recipe_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID غير صحيح']);
    exit;
}

// نتحقق أن الوصفة موجودة
$recipe = $recipeObj->getById($recipe_id);

if (!$recipe) {
    echo json_encode(['success' => false, 'message' => 'الوصفة غير موجودة']);
    exit;
}

// نزيد عدد اللايكات ونرجع العدد الجديد
$newLikes = $recipeObj->addLike($recipe_id);

// نرجع النتيجة كـ JSON
echo json_encode([
    'success' => true,
    'likes'   => $newLikes
]);
exit;
