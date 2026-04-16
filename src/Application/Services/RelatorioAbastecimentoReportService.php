<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Application\Contracts\AbastecimentoReportReadModelInterface;

final class RelatorioAbastecimentoReportService
{
    public function __construct(
        private readonly AbastecimentoReportReadModelInterface $readModel,
        private readonly RelatorioAbastecimentoCriteriaService $criteria,
        private readonly RelatorioAbastecimentoFilterService $filters
    ) {
    }

    /**
     * @param array<string, mixed> $inputFilters
     * @return list<array<string, mixed>>
     */
    public function generate(array $inputFilters): array
    {
        $criteria = $this->criteria->fromFilters($inputFilters);

        return $this->filters->filter(
            $this->readModel->fetchByCriteria($criteria),
            $criteria['secretaria'],
            $criteria['status']
        );
    }
}
