<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Factories;

use FrotaSmart\Application\Services\RelatorioAbastecimentoCriteriaService;
use FrotaSmart\Application\Services\RelatorioAbastecimentoFilterService;
use FrotaSmart\Application\Services\RelatorioAbastecimentoReportService;
use FrotaSmart\Application\Services\RelatorioAuditReportService;
use FrotaSmart\Application\Services\RelatorioAuditSummaryService;
use FrotaSmart\Application\Services\RelatorioCsvExporterService;
use FrotaSmart\Application\Services\RelatorioDatasetSelectorService;
use FrotaSmart\Application\Services\RelatorioExecutiveSummaryService;
use FrotaSmart\Application\Services\RelatorioExportService;
use FrotaSmart\Application\Services\RelatorioOperacionalFacadeDependencies;
use FrotaSmart\Application\Services\RelatorioOperationalReportService;
use FrotaSmart\Application\Services\RelatorioOperationalSummaryService;
use FrotaSmart\Application\Services\RelatorioRowTransformerService;
use FrotaSmart\Infrastructure\ReadModels\AbastecimentoReadModel;
use FrotaSmart\Infrastructure\ReadModels\ManutencaoReadModel;
use FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService;
use PDO;

final class RelatorioOperacionalDependenciesFactory
{
    public function make(PDO $connection): RelatorioOperacionalFacadeDependencies
    {
        $queries = new RelatorioOperacionalQueryService($connection);
        $abastecimentos = new AbastecimentoReadModel($connection);
        $manutencoes = new ManutencaoReadModel($connection);
        $rowTransformer = new RelatorioRowTransformerService();
        $auditSummaries = new RelatorioAuditSummaryService();
        $datasetSelector = new RelatorioDatasetSelectorService();

        $executiveSummaries = new RelatorioExecutiveSummaryService(
            $queries,
            $abastecimentos,
            $manutencoes
        );

        $abastecimentoReport = new RelatorioAbastecimentoReportService(
            $abastecimentos,
            new RelatorioAbastecimentoCriteriaService(),
            new RelatorioAbastecimentoFilterService()
        );

        $operationalReports = new RelatorioOperationalReportService(
            $queries,
            $rowTransformer
        );

        $auditReport = new RelatorioAuditReportService(
            $queries,
            $rowTransformer,
            $auditSummaries
        );

        $exportService = new RelatorioExportService(
            $abastecimentoReport,
            $operationalReports,
            $auditReport,
            $datasetSelector,
            new RelatorioCsvExporterService()
        );

        return new RelatorioOperacionalFacadeDependencies(
            $queries,
            $executiveSummaries,
            $auditReport,
            $abastecimentoReport,
            $operationalReports,
            new RelatorioOperationalSummaryService(),
            $exportService
        );
    }
}
