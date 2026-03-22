<?php
require_once __DIR__ . '/db.php';

if (!is_cli_request()) {
    http_response_code(403);
    exit('Este script só pode ser executado via CLI.' . PHP_EOL);
}

$newPassword = $argv[1] ?? ($_ENV['RESET_PASSWORD_NEW'] ?? '');
$username = $argv[2] ?? 'admin_frota';

if ($newPassword === '') {
    exit("Uso: php backend/config/reset_password.php <nova_senha> [usuario]\n");
}

echo "--- Redefinição de Senha ---\n";
echo "Usuário alvo: {$username}\n";

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $update->execute([':password' => $hash, ':id' => $user['id']]);

        echo "✅ Senha atualizada com sucesso.\n";
    } else {
        echo "❌ Usuário não encontrado. Execute o seed_admin.php primeiro.\n";
    }
} catch (PDOException $e) {
    error_log('Erro ao redefinir senha: ' . $e->getMessage());
    echo "Erro interno ao redefinir senha. Consulte os logs.\n";
}
?>
