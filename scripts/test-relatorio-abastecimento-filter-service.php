<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\RelatorioAbastecimentoFilterService();

$rows = [
    ['secretaria' => 'Saude', 'anomalia_status' => 'normal', 'placa' => 'AAA1A11'],
    ['secretaria' => 'Saude', 'anomalia_status' => 'critico', 'placa' => 'BBB2B22'],
    ['secretaria' => 'Educacao', 'anomalia_status' => 'atencao', 'placa' => 'CCC3C33'],
];

$bySecretaria = $service->filter($rows, 'Saude', null);
$byStatus = $service->filter($rows, null, 'atencao');
$byBoth = $service->filter($rows, 'Saude', 'critico');

if (count($bySecretaria) !== 2) {
    throw new RuntimeException('Filtro de abastecimentos deveria restringir corretamente por secretaria.');
}

if (count($byStatus) !== 1 || ($byStatus[0]['placa'] ?? '') !== 'CCC3C33') {
    throw new RuntimeException('Filtro de abastecimentos deveria restringir corretamente por status.');
}

if (count($byBoth) !== 1 || ($byBoth[0]['placa'] ?? '') !== 'BBB2B22') {
    throw new RuntimeException('Filtro de abastecimentos deveria combinar secretaria e status.');
}

echo "Filtro de abastecimentos validado com sucesso.\n";
