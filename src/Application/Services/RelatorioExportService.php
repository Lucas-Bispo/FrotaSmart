<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioExportService
{
    public function __construct(
        private readonly RelatorioAbastecimentoReportService $abastecimentoReport,
        private readonly RelatorioOperationalReportService $operationalReports,
        private readonly RelatorioAuditReportService $auditReport,
        private readonly RelatorioDatasetSelectorService $datasetSelector,
        private readonly RelatorioCsvExporterService $csvExporter
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function export(string $report, array $filters): string
    {
        return $this->csvExporter->export($this->datasetSelector->select($report, [
            'abastecimentos' => fn (): array => $this->abastecimentoReport->generate($filters),
            'manutencoes' => fn (): array => $this->operationalReports->manutencoes($filters),
            'viagens' => fn (): array => $this->operationalReports->viagens($filters),
            'disponibilidade' => fn (): array => $this->operationalReports->disponibilidade($filters),
            'documentacao' => fn (): array => $this->operationalReports->documentacao($filters),
            'transparencia' => fn (): array => $this->operationalReports->transparenciaPublica($filters),
            'auditoria' => fn (): array => $this->auditReport->report($filters),
        ]));
    }
}
