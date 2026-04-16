<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\UserRegistrationInputService();

$payload = $service->validate([
    'username' => 'admin.local',
    'password' => 'SenhaForte@2026',
    'role' => 'admin',
], ['admin', 'gerente', 'motorista', 'auditor']);

if ($payload['username'] !== 'admin.local' || $payload['role'] !== 'admin') {
    throw new RuntimeException('UserRegistrationInputService deveria preservar username e perfil validos.');
}

try {
    $service->validate([
        'username' => 'abc',
        'password' => 'SenhaForte@2026',
        'role' => 'admin',
    ], ['admin', 'gerente']);

    throw new RuntimeException('UserRegistrationInputService deveria rejeitar username curto.');
} catch (\DomainException $exception) {
    if ($exception->getMessage() !== 'O usuario deve ter entre 4 e 50 caracteres, usando apenas letras, numeros, ponto, underline ou hifen.') {
        throw new RuntimeException('UserRegistrationInputService retornou mensagem inesperada para username invalido.');
    }
}

try {
    $service->validate([
        'username' => 'admin.local',
        'password' => 'fraca',
        'role' => 'admin',
    ], ['admin', 'gerente']);

    throw new RuntimeException('UserRegistrationInputService deveria rejeitar senha fraca.');
} catch (\DomainException $exception) {
    if ($exception->getMessage() !== 'A senha deve ter no minimo 12 caracteres e incluir maiuscula, minuscula, numero e simbolo.') {
        throw new RuntimeException('UserRegistrationInputService retornou mensagem inesperada para senha fraca.');
    }
}

echo "UserRegistrationInputService validado com sucesso.\n";
