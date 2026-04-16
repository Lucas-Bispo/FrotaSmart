<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\RelatorioAbastecimentoCriteriaService();

$criteria = $service->fromFilters([
    'veiculo_id' => '12',
    'data_inicio' => ' 2026-04-01 ',
    'data_fim' => '',
    'secretaria' => ' Saude ',
    'status' => 'critico',
]);

if (($criteria['veiculo_id'] ?? null) !== 12) {
    throw new RuntimeException('Criterio de abastecimento deveria normalizar veiculo_id numerico valido.');
}

if (($criteria['data_inicio'] ?? null) !== '2026-04-01'
    || ! array_key_exists('data_fim', $criteria)
    || $criteria['data_fim'] !== null) {
    throw new RuntimeException('Criterio de abastecimento deveria normalizar datas opcionais.');
}

if (($criteria['secretaria'] ?? null) !== 'Saude' || ($criteria['status'] ?? null) !== 'critico') {
    throw new RuntimeException('Criterio de abastecimento deveria normalizar filtros textuais.');
}

$emptyCriteria = $service->fromFilters([
    'veiculo_id' => '0',
    'secretaria' => '   ',
]);

if (! array_key_exists('veiculo_id', $emptyCriteria)
    || $emptyCriteria['veiculo_id'] !== null
    || ! array_key_exists('secretaria', $emptyCriteria)
    || $emptyCriteria['secretaria'] !== null) {
    throw new RuntimeException('Criterio de abastecimento deveria descartar filtros vazios ou invalidos.');
}

echo "Criterios de abastecimento validados com sucesso.\n";
