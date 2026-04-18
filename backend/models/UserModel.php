<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

final class UserModel
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
    }

    /**
     * @return array{id:int,username:string,role:string}|false
     */
    public function login(string $username, string $password): array|false
    {
        $stmt = $this->connection->prepare(
            'SELECT id, username, password, role
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! is_array($user) || ! password_verify($password, (string) $user['password'])) {
            return false;
        }

        if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
            $this->rehashPassword((int) $user['id'], $password);
        }

        return [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'role' => (string) $user['role'],
        ];
    }

    public function register(string $username, string $password, string $role = 'gerente'): void
    {
        $normalizedRole = in_array($role, valid_roles(), true) ? $role : 'gerente';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->connection->prepare(
            'INSERT INTO users (username, password, role)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $hash, $normalizedRole]);
    }

    private function rehashPassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    private function resolveLegacyConnection(): PDO
    {
        return database_connection();
    }
}
