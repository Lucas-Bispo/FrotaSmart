<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\RelatorioQueryCriteriaService();

$operational = $service->forOperationalReport([
    'data_inicio' => ' 2026-04-01 ',
    'data_fim' => '',
    'secretaria' => ' Transportes ',
    'veiculo_id' => '7',
    'status' => 'aberta',
]);

if (($operational['data_inicio'] ?? null) !== '2026-04-01'
    || ! array_key_exists('data_fim', $operational)
    || $operational['data_fim'] !== null
    || ($operational['secretaria'] ?? null) !== 'Transportes'
    || ($operational['veiculo_id'] ?? null) !== 7
    || ($operational['status'] ?? null) !== 'aberta') {
    throw new RuntimeException('Criterios operacionais deveriam normalizar filtros compartilhados de relatorio.');
}

$audit = $service->forAuditReport([
    'data_inicio' => '2026-04-10',
    'data_fim' => ' 2026-04-15 ',
    'ator' => ' admin ',
    'evento' => ' exported ',
    'status' => 'create',
    'tipo_alvo' => 'veiculo',
]);

if (($audit['data_inicio'] ?? null) !== '2026-04-10'
    || ($audit['data_fim'] ?? null) !== '2026-04-15'
    || ($audit['ator'] ?? null) !== 'admin'
    || ($audit['evento'] ?? null) !== 'exported'
    || ($audit['status'] ?? null) !== 'create'
    || ($audit['tipo_alvo'] ?? null) !== 'veiculo') {
    throw new RuntimeException('Criterios de auditoria deveriam normalizar filtros textuais compartilhados.');
}

$empty = $service->forOperationalReport([
    'veiculo_id' => '0',
    'secretaria' => '   ',
]);

if (! array_key_exists('veiculo_id', $empty)
    || $empty['veiculo_id'] !== null
    || ! array_key_exists('secretaria', $empty)
    || $empty['secretaria'] !== null) {
    throw new RuntimeException('Criterios compartilhados deveriam descartar valores vazios ou invalidos.');
}

echo "Criterios compartilhados de relatorio validados com sucesso.\n";
