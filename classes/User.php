<?php
class User
{
    private $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

 
    public function register($username, $email, $password)
    {
        
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return "Username is already taken. Please choose another one.";
        }

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "This email is already registered. Try logging in instead.";
        }

    
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

       
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
        );
        $stmt->execute([$username, $email, $hashedPassword]);

        return true;
    }

    public function login($username, $password)
    {
        
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            return $user; 
        }

        return false; 
    }
}
