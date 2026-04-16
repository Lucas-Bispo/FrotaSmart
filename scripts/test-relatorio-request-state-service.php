<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\RelatorioRequestStateService();

$filters = $service->captureFilters([
    'data_inicio' => 20260415,
    'secretaria' => 'Saude',
    'evento' => 'relatorio.exported',
]);

if (($filters['data_inicio'] ?? null) !== '20260415'
    || ($filters['secretaria'] ?? null) !== 'Saude'
    || ($filters['evento'] ?? null) !== 'relatorio.exported'
    || ($filters['data_fim'] ?? null) !== '') {
    throw new RuntimeException('Estado de request deveria capturar filtros em formato consistente para a view.');
}

$report = $service->resolveReport('inexistente', [
    'abastecimentos' => 'Abastecimentos',
    'viagens' => 'Viagens',
]);

if ($report !== 'abastecimentos') {
    throw new RuntimeException('Estado de request deveria cair no relatorio padrao quando a aba informada for invalida.');
}

$auditFilters = $service->filtersForAudit([
    'data_inicio' => '2026-04-15',
    'secretaria' => '   ',
    'evento' => 'relatorio.exported',
]);

if (($auditFilters['data_inicio'] ?? null) !== '2026-04-15'
    || ($auditFilters['evento'] ?? null) !== 'relatorio.exported'
    || array_key_exists('secretaria', $auditFilters)) {
    throw new RuntimeException('Estado de request deveria preservar apenas filtros preenchidos para auditoria.');
}

echo "Estado de request de relatorios validado com sucesso.\n";
