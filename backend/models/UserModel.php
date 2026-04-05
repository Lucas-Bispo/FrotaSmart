<?php
require_once __DIR__ . '/../config/db.php';

class UserModel {
    public function login($username, $password) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $this->rehashPassword((int) $user['id'], $password);
            }

            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function register($username, $password, $role = 'gerente') {
        global $pdo;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        
        $valid_roles = valid_roles();
        if (!in_array($role, $valid_roles, true)) {
             $role = 'gerente';
        }
        $stmt->execute([$username, $hash, $role]);
    }

    private function rehashPassword(int $id, string $password): void
    {
        global $pdo;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }
}
?>
