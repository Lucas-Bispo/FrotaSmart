<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class RelatorioOperacionalModel
{
    private PDO $connection;
    private \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService $queries;
    private \FrotaSmart\Application\Services\RelatorioExecutiveSummaryService $executiveSummaries;
    private \FrotaSmart\Application\Services\RelatorioAuditReportService $auditReport;
    private \FrotaSmart\Application\Services\RelatorioOperationalSummaryService $operationalSummaries;
    private \FrotaSmart\Application\Services\RelatorioOperationalReportService $operationalReports;
    private \FrotaSmart\Application\Services\RelatorioAbastecimentoReportService $abastecimentoReport;
    private \FrotaSmart\Application\Services\RelatorioExportService $exportService;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
        $dependencies = (new \FrotaSmart\Infrastructure\Factories\RelatorioOperacionalDependenciesFactory())
            ->make($this->connection);

        $this->queries = $dependencies->queries;
        $this->executiveSummaries = $dependencies->executiveSummaries;
        $this->auditReport = $dependencies->auditReport;
        $this->abastecimentoReport = $dependencies->abastecimentoReport;
        $this->operationalReports = $dependencies->operationalReports;
        $this->operationalSummaries = $dependencies->operationalSummaries;
        $this->exportService = $dependencies->exportService;
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
        return $this->operationalReports->manutencoes($filters);
    }

    public function getViagemReport(array $filters): array
    {
        return $this->operationalReports->viagens($filters);
    }

    public function getDisponibilidadeReport(array $filters): array
    {
        return $this->operationalReports->disponibilidade($filters);
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
        return $this->auditReport->report($filters);
    }

    public function getAuditSummary(array $filters): array
    {
        return $this->auditReport->summary($filters);
    }

    public function getAuditTargetTypes(): array
    {
        return $this->auditReport->targetTypes();
    }

    public function exportCsv(string $report, array $filters): string
    {
        return $this->exportService->export($report, $filters);
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
