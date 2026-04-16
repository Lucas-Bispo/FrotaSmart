<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Application\Contracts\AuditReportReadModelInterface;

final class RelatorioAuditReportService
{
    public function __construct(
        private readonly AuditReportReadModelInterface $readModel,
        private readonly RelatorioRowTransformerService $rowTransformer,
        private readonly RelatorioAuditSummaryService $summary
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function report(array $filters): array
    {
        return $this->rowTransformer->withAuditContextSummary($this->readModel->fetchAuditRows($filters));
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, int>
     */
    public function summary(array $filters): array
    {
        return $this->summary->summarize($this->report($filters));
    }

    /**
     * @return list<string>
     */
    public function targetTypes(): array
    {
        return $this->readModel->fetchAuditTargetTypes();
    }
}
