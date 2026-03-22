<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/db.php';

if (! is_cli_request()) {
    http_response_code(403);
    exit("Este script so pode ser executado via CLI.\n");
}

$newPassword = $argv[1] ?? '';
$username = $argv[2] ?? 'admin_frota';

if ($newPassword === '') {
    exit("Uso: C:\\xampp\\php\\php.exe scripts/reset-password.php <nova_senha> [usuario]\n");
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $user) {
        exit("Usuario nao encontrado.\n");
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $update->execute([
        ':password' => $hash,
        ':id' => $user['id'],
    ]);

    echo "Senha atualizada com sucesso para o usuario informado.\n";
} catch (PDOException $e) {
    error_log('Erro ao redefinir senha: ' . $e->getMessage());
    exit("Erro ao redefinir senha. Consulte os logs.\n");
}
