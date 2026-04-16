<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioAbastecimentoCriteriaService
{
    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     veiculo_id:?int,
     *     data_inicio:?string,
     *     data_fim:?string,
     *     secretaria:?string,
     *     status:?string
     * }
     */
    public function fromFilters(array $filters): array
    {
        return [
            'veiculo_id' => $this->normalizeOptionalInt($filters['veiculo_id'] ?? null),
            'data_inicio' => $this->normalizeOptionalString($filters['data_inicio'] ?? null),
            'data_fim' => $this->normalizeOptionalString($filters['data_fim'] ?? null),
            'secretaria' => $this->normalizeOptionalString($filters['secretaria'] ?? null),
            'status' => $this->normalizeOptionalString($filters['status'] ?? null),
        ];
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
