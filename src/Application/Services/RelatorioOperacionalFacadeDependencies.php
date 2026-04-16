<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService;

final class RelatorioOperacionalFacadeDependencies
{
    public function __construct(
        public readonly RelatorioOperacionalQueryService $queries,
        public readonly RelatorioExecutiveSummaryService $executiveSummaries,
        public readonly RelatorioAuditReportService $auditReport,
        public readonly RelatorioAbastecimentoReportService $abastecimentoReport,
        public readonly RelatorioOperationalReportService $operationalReports,
        public readonly RelatorioOperationalSummaryService $operationalSummaries,
        public readonly RelatorioExportService $exportService
    ) {
    }
}
