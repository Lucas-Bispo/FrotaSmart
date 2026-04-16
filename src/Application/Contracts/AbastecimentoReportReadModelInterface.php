<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Contracts;

interface AbastecimentoReportReadModelInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function fetchAll(?int $veiculoId = null, ?string $dataInicio = null, ?string $dataFim = null): array;
}
