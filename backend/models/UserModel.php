<?php
require_once __DIR__ . '/../config/db.php';

class UserModel {
    public function login($username, $password) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    public function register($username, $password, $role = 'gerente') {
        global $pdo;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        
        $valid_roles = ['admin', 'gerente', 'motorista'];
        if (!in_array($role, $valid_roles)) {
             $role = 'gerente';
        }
        $stmt->execute([$username, $hash, $role]);
    }
}
?>