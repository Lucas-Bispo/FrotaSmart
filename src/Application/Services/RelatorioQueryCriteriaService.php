<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioQueryCriteriaService
{
    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     data_inicio:?string,
     *     data_fim:?string,
     *     secretaria:?string,
     *     veiculo_id:?int,
     *     status:?string
     * }
     */
    public function forOperationalReport(array $filters): array
    {
        return [
            'data_inicio' => $this->normalizeOptionalString($filters['data_inicio'] ?? null),
            'data_fim' => $this->normalizeOptionalString($filters['data_fim'] ?? null),
            'secretaria' => $this->normalizeOptionalString($filters['secretaria'] ?? null),
            'veiculo_id' => $this->normalizeOptionalInt($filters['veiculo_id'] ?? null),
            'status' => $this->normalizeOptionalString($filters['status'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     data_inicio:?string,
     *     data_fim:?string,
     *     ator:?string,
     *     evento:?string,
     *     status:?string,
     *     tipo_alvo:?string
     * }
     */
    public function forAuditReport(array $filters): array
    {
        return [
            'data_inicio' => $this->normalizeOptionalString($filters['data_inicio'] ?? null),
            'data_fim' => $this->normalizeOptionalString($filters['data_fim'] ?? null),
            'ator' => $this->normalizeOptionalString($filters['ator'] ?? null),
            'evento' => $this->normalizeOptionalString($filters['evento'] ?? null),
            'status' => $this->normalizeOptionalString($filters['status'] ?? null),
            'tipo_alvo' => $this->normalizeOptionalString($filters['tipo_alvo'] ?? null),
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
