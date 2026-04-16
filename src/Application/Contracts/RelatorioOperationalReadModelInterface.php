<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Contracts;

interface RelatorioOperationalReadModelInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchManutencaoReport(array $filters): array;

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchViagemReport(array $filters): array;

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchDisponibilidadeReport(array $filters): array;
}
