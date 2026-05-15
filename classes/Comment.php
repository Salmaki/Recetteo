<?php
/**
 * classes/Comment.php
 * كلاس التعليقات - يحتوي على العمليات المتعلقة بتعليقات الوصفات
 */

class Comment {

    private $pdo;

    /**
     * نمرر كائن PDO عند إنشاء الكائن
     * @param PDO 
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * جلب كل التعليقات الخاصة بوصفة معينة
     * @param int $recipe_id معرّف الوصفة
     * @return array مصفوفة التعليقات
     */
    public function getByRecipeId($recipe_id) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM comments WHERE recipe_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$recipe_id]);
        return $stmt->fetchAll();
    }

    /**
     * إضافة تعليق جديد
     * @param int    $recipe_id معرّف الوصفة
     * @param string $author    اسم صاحب التعليق
     * @param string $content   نص التعليق
     */
    public function create($recipe_id, $author, $content) {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (recipe_id, author, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$recipe_id, $author, $content]);
    }
}
