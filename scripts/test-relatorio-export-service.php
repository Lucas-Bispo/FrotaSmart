<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$abastecimentoReadModel = new class () implements \FrotaSmart\Application\Contracts\AbastecimentoReportReadModelInterface {
    public function fetchByCriteria(array $criteria): array
    {
        return [
            ['tipo' => 'abastecimento', 'secretaria' => 'Saude', 'anomalia_status' => 'critico'],
        ];
    }
};

$operationalReadModel = new class () implements \FrotaSmart\Application\Contracts\RelatorioOperationalReadModelInterface {
    public function fetchManutencaoReport(array $filters): array
    {
        return [['tipo' => 'manutencao']];
    }

    public function fetchViagemReport(array $filters): array
    {
        return [['tipo' => 'viagem', 'km_saida' => 10, 'km_chegada' => 25]];
    }

    public function fetchDisponibilidadeReport(array $filters): array
    {
        return [['tipo' => 'disponibilidade', 'deleted_at' => null, 'status' => 'ativo']];
    }

    public function fetchDocumentacaoReport(array $filters): array
    {
        return [[
            'veiculo_id' => 7,
            'placa' => 'ABC1D23',
            'modelo' => 'Sprinter',
            'secretaria_lotada' => 'Saude',
            'documentos_observacoes' => '',
            'documento_tipo' => 'CRLV',
            'vencimento' => '2026-04-28',
            'situacao_documento' => 'vencendo',
        ]];
    }
};

$auditReadModel = new class () implements \FrotaSmart\Application\Contracts\AuditReportReadModelInterface {
    public function fetchAuditRows(array $filters): array
    {
        return [['tipo' => 'auditoria', 'event' => 'relatorio.exported', 'action' => 'export', 'context_json' => '{}']];
    }

    public function fetchAuditTargetTypes(): array
    {
        return ['relatorio'];
    }
};

$abastecimentoReport = new \FrotaSmart\Application\Services\RelatorioAbastecimentoReportService(
    $abastecimentoReadModel,
    new \FrotaSmart\Application\Services\RelatorioAbastecimentoCriteriaService(),
    new \FrotaSmart\Application\Services\RelatorioAbastecimentoFilterService()
);

$operationalReports = new \FrotaSmart\Application\Services\RelatorioOperationalReportService(
    $operationalReadModel,
    new \FrotaSmart\Application\Services\RelatorioRowTransformerService()
);

$auditReport = new \FrotaSmart\Application\Services\RelatorioAuditReportService(
    $auditReadModel,
    new \FrotaSmart\Application\Services\RelatorioRowTransformerService(),
    new \FrotaSmart\Application\Services\RelatorioAuditSummaryService()
);

$service = new \FrotaSmart\Application\Services\RelatorioExportService(
    $abastecimentoReport,
    $operationalReports,
    $auditReport,
    new \FrotaSmart\Application\Services\RelatorioDatasetSelectorService(),
    new \FrotaSmart\Application\Services\RelatorioCsvExporterService()
);

$csvViagem = $service->export('viagens', ['status' => 'concluida']);
$csvAbastecimento = $service->export('abastecimentos', ['status' => 'critico']);
$csvDocumentacao = $service->export('documentacao', ['status' => 'vencendo']);
$csvUnknown = $service->export('inexistente', []);

if (! str_contains($csvViagem, 'viagem')) {
    throw new RuntimeException('Export service deveria serializar o dataset operacional solicitado.');
}

if (! str_contains($csvAbastecimento, 'critico')) {
    throw new RuntimeException('Export service deveria repassar filtros ao fluxo de abastecimentos.');
}

if (! str_contains($csvDocumentacao, 'ABC1D23') || ! str_contains($csvDocumentacao, 'vencendo')) {
    throw new RuntimeException('Export service deveria serializar o dataset documental consolidado.');
}

if (! str_contains($csvUnknown, 'sem_dados')) {
    throw new RuntimeException('Export service deveria manter o comportamento de CSV vazio para relatorio desconhecido.');
}

echo "Service de exportacao de relatorio validado com sucesso.\n";
