<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Application\Contracts\RelatorioOperationalReadModelInterface;

final class RelatorioOperationalReportService
{
    public function __construct(
        private readonly RelatorioOperationalReadModelInterface $readModel,
        private readonly RelatorioRowTransformerService $rowTransformer
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function manutencoes(array $filters): array
    {
        return $this->readModel->fetchManutencaoReport($filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function viagens(array $filters): array
    {
        return $this->rowTransformer->withViagemMetrics($this->readModel->fetchViagemReport($filters));
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function disponibilidade(array $filters): array
    {
        return $this->rowTransformer->withDisponibilidadeStatus($this->readModel->fetchDisponibilidadeReport($filters));
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function documentacao(array $filters): array
    {
        return $this->rowTransformer->withDocumentacaoResumo(
            $this->readModel->fetchDocumentacaoReport($filters),
            $filters
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function transparenciaPublica(array $filters): array
    {
        return $this->rowTransformer->withTransparenciaClassificacao(
            $this->readModel->fetchTransparenciaPublicaReport($filters)
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function checklists(array $filters): array
    {
        return $this->rowTransformer->withChecklistResumo(
            $this->readModel->fetchChecklistReport($filters)
        );
    }
}
