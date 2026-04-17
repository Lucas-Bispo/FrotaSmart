<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/security.php';
require_once __DIR__ . '/../backend/models/UserModel.php';

$model = new UserModel();

global $pdo;

$username = 'teste_user_model_' . date('YmdHis');
$password = 'SenhaForte@2026';

$pdo->prepare('DELETE FROM users WHERE username = ?')->execute([$username]);

$model->register($username, $password, 'perfil_invalido');

$stmt = $pdo->prepare('SELECT id, username, role FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$created = $stmt->fetch(PDO::FETCH_ASSOC);

if (! is_array($created)) {
    throw new RuntimeException('Usuario de teste nao foi criado.');
}

if (($created['role'] ?? '') !== 'gerente') {
    throw new RuntimeException('UserModel deveria normalizar perfil invalido para gerente.');
}

$authenticated = $model->login($username, $password);
if ($authenticated === false) {
    throw new RuntimeException('UserModel deveria autenticar usuario valido.');
}

if (($authenticated['username'] ?? '') !== $username || ($authenticated['role'] ?? '') !== 'gerente') {
    throw new RuntimeException('UserModel retornou dados inesperados apos autenticacao.');
}

$failed = $model->login($username, 'SenhaIncorreta@2026');
if ($failed !== false) {
    throw new RuntimeException('UserModel nao deveria autenticar com senha incorreta.');
}

$pdo->prepare('DELETE FROM users WHERE username = ?')->execute([$username]);

echo "UserModel validado com sucesso.\n";
