<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Contracts;

interface AbastecimentoReportReadModelInterface
{
    /**
     * @param array{veiculo_id:?int,data_inicio:?string,data_fim:?string} $criteria
     * @return list<array<string, mixed>>
     */
    public function fetchByCriteria(array $criteria): array;
}
