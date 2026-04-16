<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioRequestStateService
{
    /**
     * @param array<string, mixed> $query
     * @return array<string, string>
     */
    public function captureFilters(array $query): array
    {
        return [
            'data_inicio' => $this->stringValue($query['data_inicio'] ?? ''),
            'data_fim' => $this->stringValue($query['data_fim'] ?? ''),
            'secretaria' => $this->stringValue($query['secretaria'] ?? ''),
            'veiculo_id' => $this->stringValue($query['veiculo_id'] ?? ''),
            'status' => $this->stringValue($query['status'] ?? ''),
            'ator' => $this->stringValue($query['ator'] ?? ''),
            'evento' => $this->stringValue($query['evento'] ?? ''),
            'tipo_alvo' => $this->stringValue($query['tipo_alvo'] ?? ''),
        ];
    }

    /**
     * @param array<string, string> $reportLabels
     */
    public function resolveReport(string $requestedReport, array $reportLabels): string
    {
        return isset($reportLabels[$requestedReport]) ? $requestedReport : 'abastecimentos';
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, string>
     */
    public function filtersForAudit(array $filters): array
    {
        return array_filter(
            $filters,
            static fn (string $value): bool => trim($value) !== ''
        );
    }

    private function stringValue(mixed $value): string
    {
        return (string) $value;
    }
}
