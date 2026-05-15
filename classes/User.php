<?php


class User {

    // كائن الاتصال بقاعدة البيانات
    private $pdo;

    /**
     * نمرر كائن PDO عند إنشاء الكائن
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * التحقق هل البريد الإلكتروني مستخدم مسبقاً
     * @param string $email البريد الإلكتروني
     * @return bool true = مستخدم مسبقاً
     */
    public function isEmailTaken($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    /**
     * تسجيل مستخدم جديد
     * @param string $name     الاسم
     * @param string $email    البريد الإلكتروني
     * @param string $password كلمة المرور (غير مشفّرة)
     * @return int|false معرّف المستخدم الجديد أو false إذا فشل
     */
    public function register($name, $email, $password) {
        // نتحقق أن البريد غير مستخدم مسبقاً
        if ($this->isEmailTaken($email)) {
            return false;
        }

        // نشفّر كلمة المرور (لا نحفظها كنص صريح أبداً!)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email, password, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $email, $hashedPassword]);

        // نرجع معرّف المستخدم الجديد
        return $this->pdo->lastInsertId();
    }

    /**
     * تسجيل الدخول - نتحقق من البريد وكلمة المرور
     * @param string $email    البريد الإلكتروني
     * @param string $password كلمة المرور (غير مشفّرة)
     * @return array|false بيانات المستخدم أو false إذا فشل
     */
    public function login($email, $password) {
        // نجلب بيانات المستخدم عن طريق البريد
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // نتحقق من وجود المستخدم وصحة كلمة المرور
        if ($user && password_verify($password, $user['password'])) {
            return $user; // تسجيل الدخول ناجح
        }

        return false; // بيانات غير صحيحة
    }

    /**
     * جلب بيانات مستخدم عن طريق الـ ID
     * @param int $id معرّف المستخدم
     * @return array|false بيانات المستخدم
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
