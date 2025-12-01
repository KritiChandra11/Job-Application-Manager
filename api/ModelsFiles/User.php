<?php
require_once 'config.php';

class User {
    // Get user by ID
    public static function getById($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Get user by email
    public static function getByEmail($email) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    // Create a new user
    public static function create($username, $email, $password) {
        global $pdo;
        
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $password_hash, $created_at]);
    }
    
    // Check if password is valid for a user
    public static function checkPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }
}
?>
