<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\ParceiroOperacionalInputService();

$payload = $service->validate([
    'nome_fantasia' => 'Posto Central',
    'razao_social' => 'Posto Central LTDA',
    'cnpj' => '12.345.678/0001-90',
    'tipo' => 'posto_combustivel',
    'telefone' => '62 3333-4444',
    'endereco' => 'Rua Principal, 100',
    'contato_responsavel' => 'Ana Paula',
    'status' => 'ativo',
    'observacoes' => '',
]);

if ($payload['cnpj'] !== '12345678000190') {
    throw new RuntimeException('ParceiroOperacionalInputService deveria normalizar o CNPJ.');
}

if ($payload['observacoes'] !== null) {
    throw new RuntimeException('ParceiroOperacionalInputService deveria transformar texto opcional vazio em null.');
}

try {
    $service->validate([
        'nome_fantasia' => 'Oficina Centro',
        'razao_social' => 'Oficina Centro LTDA',
        'cnpj' => '12345678000190',
        'tipo' => 'transportadora',
        'status' => 'ativo',
    ]);

    throw new RuntimeException('ParceiroOperacionalInputService deveria rejeitar tipo invalido.');
} catch (\DomainException $exception) {
    if ($exception->getMessage() !== 'Informe um tipo de parceiro valido.') {
        throw new RuntimeException('ParceiroOperacionalInputService retornou mensagem inesperada para tipo invalido.');
    }
}

echo "ParceiroOperacionalInputService validado com sucesso.\n";
