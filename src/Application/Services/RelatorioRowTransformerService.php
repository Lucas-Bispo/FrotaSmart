<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioRowTransformerService
{
    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function withViagemMetrics(array $rows): array
    {
        foreach ($rows as &$row) {
            $kmSaida = (int) ($row['km_saida'] ?? 0);
            $kmChegada = isset($row['km_chegada']) ? (int) $row['km_chegada'] : null;
            $row['km_percorrido'] = ($kmChegada !== null && $kmChegada >= $kmSaida) ? $kmChegada - $kmSaida : null;
        }
        unset($row);

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function withDisponibilidadeStatus(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['situacao_disponibilidade'] = ! empty($row['deleted_at'])
                ? 'arquivado'
                : ((string) ($row['status'] ?? '') === 'manutencao' ? 'indisponivel_manutencao' : 'disponivel_operacao');
        }
        unset($row);

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function withAuditContextSummary(array $rows): array
    {
        foreach ($rows as &$row) {
            $context = $this->decodeAuditContext($row['context_json'] ?? null);
            $row['context_summary'] = $this->summarizeAuditContext($context);
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAuditContext(mixed $json): array
    {
        if (! is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function summarizeAuditContext(array $context): string
    {
        if ($context === []) {
            return 'Sem contexto adicional.';
        }

        $parts = [];
        foreach ($context as $key => $value) {
            if (in_array($key, ['request_uri'], true)) {
                continue;
            }

            if (is_array($value)) {
                $rendered = implode('; ', array_map(static fn (mixed $item): string => (string) $item, $value));
            } elseif (is_bool($value)) {
                $rendered = $value ? 'sim' : 'nao';
            } elseif ($value === null) {
                continue;
            } else {
                $rendered = trim((string) $value);
            }

            if ($rendered === '') {
                continue;
            }

            $parts[] = str_replace('_', ' ', (string) $key) . ': ' . $rendered;

            if (count($parts) >= 4) {
                break;
            }
        }

        return $parts === [] ? 'Sem contexto adicional.' : implode(' | ', $parts);
    }
}
