<?php
// auth.php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    public function login($email, $password) {
        $stmt = $this->db->conn->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function register($username, $email, $password, $phone) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->conn->prepare(
            "INSERT INTO Users (username, email, password, phone_number) 
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$username, $email, $hashedPassword, $phone]);
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    }
}
?>