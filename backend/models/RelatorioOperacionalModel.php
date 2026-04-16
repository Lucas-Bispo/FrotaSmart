<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class RelatorioOperacionalModel
{
    private PDO $connection;
    private \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService $queries;
    private \FrotaSmart\Infrastructure\ReadModels\AbastecimentoReadModel $abastecimentos;
    private \FrotaSmart\Infrastructure\ReadModels\ManutencaoReadModel $manutencoes;
    private \FrotaSmart\Application\Services\RelatorioExecutiveSummaryService $executiveSummaries;
    private \FrotaSmart\Application\Services\RelatorioAuditSummaryService $auditSummaries;
    private \FrotaSmart\Application\Services\RelatorioCsvExporterService $csvExporter;
    private \FrotaSmart\Application\Services\RelatorioOperationalSummaryService $operationalSummaries;
    private \FrotaSmart\Application\Services\RelatorioDatasetSelectorService $datasetSelector;
    private \FrotaSmart\Application\Services\RelatorioRowTransformerService $rowTransformer;
    private \FrotaSmart\Application\Services\RelatorioAbastecimentoReportService $abastecimentoReport;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
        $this->queries = new \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService($this->connection);
        $this->abastecimentos = new \FrotaSmart\Infrastructure\ReadModels\AbastecimentoReadModel($this->connection);
        $this->manutencoes = new \FrotaSmart\Infrastructure\ReadModels\ManutencaoReadModel($this->connection);
        $this->executiveSummaries = new \FrotaSmart\Application\Services\RelatorioExecutiveSummaryService(
            $this->queries,
            $this->abastecimentos,
            $this->manutencoes
        );
        $this->auditSummaries = new \FrotaSmart\Application\Services\RelatorioAuditSummaryService();
        $this->csvExporter = new \FrotaSmart\Application\Services\RelatorioCsvExporterService();
        $this->operationalSummaries = new \FrotaSmart\Application\Services\RelatorioOperationalSummaryService();
        $this->datasetSelector = new \FrotaSmart\Application\Services\RelatorioDatasetSelectorService();
        $this->rowTransformer = new \FrotaSmart\Application\Services\RelatorioRowTransformerService();
        $this->abastecimentoReport = new \FrotaSmart\Application\Services\RelatorioAbastecimentoReportService(
            $this->abastecimentos,
            new \FrotaSmart\Application\Services\RelatorioAbastecimentoCriteriaService(),
            new \FrotaSmart\Application\Services\RelatorioAbastecimentoFilterService()
        );
    }

    public function getSecretarias(): array
    {
        return $this->queries->fetchSecretarias();
    }

    public function getVeiculos(): array
    {
        return $this->queries->fetchVeiculos();
    }

    public function getAbastecimentoReport(array $filters): array
    {
        return $this->abastecimentoReport->generate($filters);
    }

    public function getManutencaoReport(array $filters): array
    {
        return $this->queries->fetchManutencaoReport($filters);
    }

    public function getViagemReport(array $filters): array
    {
        return $this->rowTransformer->withViagemMetrics($this->queries->fetchViagemReport($filters));
    }

    public function getDisponibilidadeReport(array $filters): array
    {
        return $this->rowTransformer->withDisponibilidadeStatus($this->queries->fetchDisponibilidadeReport($filters));
    }

    public function getResumo(array $filters): array
    {
        return $this->operationalSummaries->summarize(
            $this->getAbastecimentoReport($filters),
            $this->getManutencaoReport($filters),
            $this->getViagemReport($filters),
            $this->getDisponibilidadeReport($filters)
        );
    }

    public function getExecutiveSummaryBySecretaria(?string $dataInicio = null, ?string $dataFim = null): array
    {
        return $this->executiveSummaries->buildBySecretaria($dataInicio, $dataFim);
    }

    public function getExecutiveSummaryByVeiculo(?string $dataInicio = null, ?string $dataFim = null, int $limit = 8): array
    {
        return $this->executiveSummaries->buildByVeiculo($dataInicio, $dataFim, $limit);
    }

    public function getAuditReport(array $filters): array
    {
        return $this->fetchAuditRows($filters);
    }

    public function getAuditSummary(array $filters): array
    {
        return $this->auditSummaries->summarize($this->fetchAuditRows($filters));
    }

    public function getAuditTargetTypes(): array
    {
        return $this->queries->fetchAuditTargetTypes();
    }

    public function exportCsv(string $report, array $filters): string
    {
        return $this->csvExporter->export($this->resolveReportRows($report, $filters));
    }

    private function fetchAuditRows(array $filters): array
    {
        return $this->rowTransformer->withAuditContextSummary($this->queries->fetchAuditRows($filters));
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    private function resolveReportRows(string $report, array $filters): array
    {
        return $this->datasetSelector->select($report, [
            'abastecimentos' => fn (): array => $this->getAbastecimentoReport($filters),
            'manutencoes' => fn (): array => $this->getManutencaoReport($filters),
            'viagens' => fn (): array => $this->getViagemReport($filters),
            'disponibilidade' => fn (): array => $this->getDisponibilidadeReport($filters),
            'auditoria' => fn (): array => $this->getAuditReport($filters),
        ]);
    }

    private function resolveLegacyConnection(): PDO
    {
        global $pdo;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        throw new RuntimeException('Conexao PDO indisponivel para RelatorioOperacionalModel.');
    }
}
