<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\AuthLoginInputService();

$payload = $service->validate([
    'username' => 'admin_frota',
    'password' => 'SenhaQualquer',
]);

if ($payload['username'] !== 'admin_frota' || $payload['password'] !== 'SenhaQualquer') {
    throw new RuntimeException('AuthLoginInputService deveria preservar credenciais preenchidas.');
}

try {
    $service->validate([
        'username' => '   ',
        'password' => '',
    ]);

    throw new RuntimeException('AuthLoginInputService deveria rejeitar credenciais vazias.');
} catch (\DomainException $exception) {
    if ($exception->getMessage() !== 'Informe usuario e senha.') {
        throw new RuntimeException('AuthLoginInputService retornou mensagem inesperada para credenciais vazias.');
    }
}

echo "AuthLoginInputService validado com sucesso.\n";
