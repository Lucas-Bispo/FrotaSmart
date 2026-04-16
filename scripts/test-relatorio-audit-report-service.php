<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$readModel = new class () implements \FrotaSmart\Application\Contracts\AuditReportReadModelInterface {
    /** @var array<string, mixed> */
    public array $lastFilters = [];

    public function fetchAuditRows(array $filters): array
    {
        $this->lastFilters = $filters;

        return [
            [
                'event' => 'relatorio.exported',
                'action' => 'export',
                'actor' => 'alice',
                'context_json' => '{"report":"auditoria","status":"ok"}',
            ],
            [
                'event' => 'viagem.created_blocked',
                'action' => 'blocked',
                'actor' => 'bob',
                'context_json' => '{"blocked_reasons":["CNH vencida"]}',
            ],
        ];
    }

    public function fetchAuditTargetTypes(): array
    {
        return ['relatorio', 'viagem'];
    }
};

$service = new \FrotaSmart\Application\Services\RelatorioAuditReportService(
    $readModel,
    new \FrotaSmart\Application\Services\RelatorioRowTransformerService(),
    new \FrotaSmart\Application\Services\RelatorioAuditSummaryService()
);

$filters = [
    'data_inicio' => '2026-04-15',
    'tipo_alvo' => 'relatorio',
];

$rows = $service->report($filters);
$summary = $service->summary($filters);
$targetTypes = $service->targetTypes();

if (($readModel->lastFilters['tipo_alvo'] ?? null) !== 'relatorio') {
    throw new RuntimeException('Service de auditoria deveria repassar os filtros recebidos ao read model.');
}

if (! str_contains((string) ($rows[0]['context_summary'] ?? ''), 'report: auditoria')) {
    throw new RuntimeException('Service de auditoria deveria enriquecer as linhas com resumo de contexto.');
}

if (($summary['exportacoes'] ?? 0) !== 1 || ($summary['bloqueios'] ?? 0) !== 1) {
    throw new RuntimeException('Service de auditoria deveria consolidar resumo sobre as linhas transformadas.');
}

if ($targetTypes !== ['relatorio', 'viagem']) {
    throw new RuntimeException('Service de auditoria deveria expor os tipos de alvo informados pelo read model.');
}

echo "Service de auditoria de relatorio validado com sucesso.\n";
