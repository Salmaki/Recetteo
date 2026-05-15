<?php


class Recipe {

    private $pdo;

    /**
     * نمرر كائن PDO عند إنشاء الكائن
     * @param PDO $pdo كائن الاتصال
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * جلب كل الوصفات مع إمكانية الفلترة حسب التصنيف
     * @param string $category التصنيف (فارغ = الكل)
     * @return array مصفوفة الوصفات
     */
    public function getAll($category = '') {
        if ($category !== '') {
            // فلترة حسب التصنيف المحدد
            $stmt = $this->pdo->prepare(
                "SELECT * FROM recipes WHERE category = ? ORDER BY created_at DESC"
            );
            $stmt->execute([$category]);
        } else {
         
            $stmt = $this->pdo->query(
                "SELECT * FROM recipes ORDER BY created_at DESC"
            );
        }
        return $stmt->fetchAll();
    }

    /**
     * البحث عن وصفة بالكلمة المفتاحية
     * @param string $keyword كلمة البحث
     * @return array نتائج البحث
     */
    public function search($keyword) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM recipes WHERE title LIKE ? ORDER BY created_at DESC"
        );
        // % قبل وبعد الكلمة للبحث في أي مكان في الاسم
        $stmt->execute(['%' . $keyword . '%']);
        return $stmt->fetchAll();
    }

    /**
     * جلب وصفة واحدة عن طريق الـ ID
     * @param int $id معرّف الوصفة
     * @return array|false بيانات الوصفة
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM recipes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * إضافة وصفة جديدة إلى قاعدة البيانات
     * @return int معرّف الوصفة الجديدة
     */
    public function create($title, $category, $ingredients, $instructions, $image) {
        $stmt = $this->pdo->prepare("
            INSERT INTO recipes (title, category, ingredients, instructions, image, likes, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$title, $category, $ingredients, $instructions, $image]);
        return $this->pdo->lastInsertId();
    }

    /**
     * تعديل وصفة موجودة
     */
    public function update($id, $title, $category, $ingredients, $instructions, $image) {
        $stmt = $this->pdo->prepare("
            UPDATE recipes
            SET title = ?, category = ?, ingredients = ?, instructions = ?, image = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $category, $ingredients, $instructions, $image, $id]);
    }

    /**
     * حذف وصفة وملف صورتها من السيرفر
     * @param int $id معرّف الوصفة
     */
    public function delete($id) {
        // نجلب بيانات الوصفة أولاً لنحذف ملف الصورة
        $recipe = $this->getById($id);

        // نحذف ملف الصورة إذا لم تكن الصورة الافتراضية
        if ($recipe && $recipe['image'] !== 'default.jpg') {
            $imagePath = UPLOAD_DIR . $recipe['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath); // حذف الملف من السيرفر
            }
        }

        // نحذف التعليقات المرتبطة بهذه الوصفة
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE recipe_id = ?");
        $stmt->execute([$id]);

        // نحذف الوصفة نفسها
        $stmt = $this->pdo->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * زيادة عدد اللايكات بواحد وإرجاع العدد الجديد
     * @param int $id معرّف الوصفة
     * @return int العدد الجديد
     */
    public function addLike($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE recipes SET likes = likes + 1 WHERE id = ?"
        );
        $stmt->execute([$id]);

        // نرجع العدد الجديد بعد التحديث
        $recipe = $this->getById($id);
        return $recipe['likes'];
    }

    /**
     * جلب قائمة التصنيفات الموجودة في قاعدة البيانات
     * @return array قائمة التصنيفات
     */
    public function getCategories() {
        $stmt = $this->pdo->query(
            "SELECT DISTINCT category FROM recipes ORDER BY category"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
