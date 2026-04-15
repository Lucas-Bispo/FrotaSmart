<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$auditSummaryService = new \FrotaSmart\Application\Services\RelatorioAuditSummaryService();
$csvExporter = new \FrotaSmart\Application\Services\RelatorioCsvExporterService();

$rows = [
    [
        'event' => 'veiculo.created',
        'action' => 'create',
        'actor' => 'alice',
        'context_summary' => 'placa: ABC1D23',
    ],
    [
        'event' => 'relatorio.exported',
        'action' => 'export',
        'actor' => 'bob',
        'context_summary' => 'report: auditoria',
    ],
    [
        'event' => 'viagem.created_blocked',
        'action' => 'blocked',
        'actor' => 'bob',
        'context_summary' => 'blocked reasons: Veiculo em manutencao.',
    ],
];

$summary = $auditSummaryService->summarize($rows);
$csv = $csvExporter->export($rows);
$emptyCsv = $csvExporter->export([]);

if (($summary['eventos_total'] ?? 0) !== 3) {
    throw new RuntimeException('Resumo de auditoria deveria contar todos os eventos informados.');
}

if (($summary['atores_unicos'] ?? 0) !== 2) {
    throw new RuntimeException('Resumo de auditoria deveria consolidar atores unicos.');
}

if (($summary['exportacoes'] ?? 0) !== 1 || ($summary['bloqueios'] ?? 0) !== 1 || ($summary['mutacoes'] ?? 0) !== 1) {
    throw new RuntimeException('Resumo de auditoria deveria classificar exportacoes, bloqueios e mutacoes.');
}

if (! str_contains($csv, 'context_summary') || ! str_contains($csv, 'relatorio.exported')) {
    throw new RuntimeException('Exportador CSV deveria preservar cabecalho e linhas informadas.');
}

if (! str_contains($emptyCsv, 'sem_dados')) {
    throw new RuntimeException('Exportador CSV deveria sinalizar ausencia de dados.');
}

echo "Services auxiliares de relatorio validados com sucesso.\n";
