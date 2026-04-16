<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\MotoristaInputService();

$payload = $service->validate([
    'nome' => 'Maria da Silva',
    'cpf' => '123.456.789-01',
    'telefone' => '62 99999-0000',
    'secretaria' => 'Saude',
    'cnh_numero' => ' ab12345 ',
    'cnh_categoria' => 'd',
    'cnh_vencimento' => '2026-12-31',
    'status' => 'ativo',
]);

if ($payload['cpf'] !== '12345678901') {
    throw new RuntimeException('MotoristaInputService deveria normalizar o CPF.');
}

if ($payload['cnh_numero'] !== 'AB12345') {
    throw new RuntimeException('MotoristaInputService deveria normalizar o numero da CNH.');
}

if ($payload['cnh_categoria'] !== 'D') {
    throw new RuntimeException('MotoristaInputService deveria normalizar a categoria da CNH.');
}

try {
    $service->validate([
        'nome' => 'Joao',
        'cpf' => '123',
        'secretaria' => 'Saude',
        'cnh_numero' => 'ABC1234',
        'cnh_categoria' => 'B',
        'cnh_vencimento' => '2026-12-31',
        'status' => 'ativo',
    ]);

    throw new RuntimeException('MotoristaInputService deveria rejeitar CPF invalido.');
} catch (\DomainException $exception) {
    if ($exception->getMessage() !== 'Informe um CPF valido com 11 digitos.') {
        throw new RuntimeException('MotoristaInputService retornou mensagem inesperada para CPF invalido.');
    }
}

echo "MotoristaInputService validado com sucesso.\n";
