<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/db.php';

if (! is_cli_request()) {
    http_response_code(403);
    exit("Este script so pode ser executado via CLI.\n");
}

$username = $argv[1] ?? 'admin_frota';
$newPassword = promptHidden('Nova senha: ');
$confirmation = promptHidden('Confirme a nova senha: ');

if ($newPassword === '') {
    exit("A senha nao pode ficar vazia.\n");
}

if ($newPassword !== $confirmation) {
    exit("As senhas informadas nao conferem.\n");
}

if (! hasStrongPassword($newPassword)) {
    exit("A senha deve ter no minimo 12 caracteres, com maiuscula, minuscula, numero e simbolo.\n");
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

function promptHidden(string $label): string
{
    if (! function_exists('shell_exec')) {
        fwrite(STDOUT, $label);
        return trim((string) fgets(STDIN));
    }

    fwrite(STDOUT, $label);
    shell_exec('stty -echo');
    $value = trim((string) fgets(STDIN));
    shell_exec('stty echo');
    fwrite(STDOUT, PHP_EOL);

    return $value;
}

function hasStrongPassword(string $password): bool
{
    if (strlen($password) < 12) {
        return false;
    }

    return preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[^a-zA-Z0-9]/', $password) === 1;
}
