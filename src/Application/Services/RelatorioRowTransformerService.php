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
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function withDocumentacaoResumo(array $rows, array $filters = []): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $vehicleKey = (string) ($row['veiculo_id'] ?? $row['placa'] ?? '');
            if ($vehicleKey === '') {
                continue;
            }

            $grouped[$vehicleKey] ??= [
                'veiculo_id' => $row['veiculo_id'] ?? null,
                'placa' => (string) ($row['placa'] ?? ''),
                'modelo' => (string) ($row['modelo'] ?? ''),
                'secretaria_lotada' => (string) ($row['secretaria_lotada'] ?? 'Nao informada'),
                'documentos_observacoes' => (string) ($row['documentos_observacoes'] ?? ''),
                'documentos_vencidos' => 0,
                'documentos_vencendo' => 0,
                'documentos_regulares' => 0,
                'proximo_vencimento' => null,
                'pendencias_resumo' => [],
                'documentos_monitorados' => [],
                'situacao_documental' => 'regular',
            ];

            $situacao = (string) ($row['situacao_documento'] ?? 'regular');
            $documentoLabel = (string) ($row['documento_tipo'] ?? 'Documento');
            $vencimento = (string) ($row['vencimento'] ?? '');

            $grouped[$vehicleKey]['documentos_monitorados'][] = $documentoLabel . ': ' . $vencimento;

            if ($grouped[$vehicleKey]['proximo_vencimento'] === null
                || ($vencimento !== '' && strcmp($vencimento, (string) $grouped[$vehicleKey]['proximo_vencimento']) < 0)) {
                $grouped[$vehicleKey]['proximo_vencimento'] = $vencimento;
            }

            if ($situacao === 'vencido') {
                $grouped[$vehicleKey]['documentos_vencidos']++;
                $grouped[$vehicleKey]['pendencias_resumo'][] = $documentoLabel . ' vencido em ' . $vencimento;
                $grouped[$vehicleKey]['situacao_documental'] = 'vencido';
                continue;
            }

            if ($situacao === 'vencendo') {
                $grouped[$vehicleKey]['documentos_vencendo']++;
                $grouped[$vehicleKey]['pendencias_resumo'][] = $documentoLabel . ' vence em ' . $vencimento;

                if ($grouped[$vehicleKey]['situacao_documental'] !== 'vencido') {
                    $grouped[$vehicleKey]['situacao_documental'] = 'vencendo';
                }

                continue;
            }

            $grouped[$vehicleKey]['documentos_regulares']++;
        }

        $statusFilter = trim((string) ($filters['status'] ?? ''));
        $result = [];

        foreach ($grouped as $item) {
            $item['total_pendencias'] = (int) $item['documentos_vencidos'] + (int) $item['documentos_vencendo'];
            $item['pendencias_resumo'] = $item['pendencias_resumo'] === []
                ? 'Nenhuma pendencia na janela atual.'
                : implode(' | ', $item['pendencias_resumo']);
            $item['documentos_monitorados'] = implode(' | ', $item['documentos_monitorados']);

            if ($statusFilter !== '' && $item['situacao_documental'] !== $statusFilter) {
                continue;
            }

            $result[] = $item;
        }

        usort($result, static function (array $left, array $right): int {
            $priority = ['vencido' => 0, 'vencendo' => 1, 'regular' => 2];
            $leftPriority = $priority[(string) ($left['situacao_documental'] ?? 'regular')] ?? 9;
            $rightPriority = $priority[(string) ($right['situacao_documental'] ?? 'regular')] ?? 9;

            return [$leftPriority, (string) ($left['proximo_vencimento'] ?? '9999-12-31'), (string) ($left['placa'] ?? '')]
                <=> [$rightPriority, (string) ($right['proximo_vencimento'] ?? '9999-12-31'), (string) ($right['placa'] ?? '')];
        });

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function withTransparenciaClassificacao(array $rows): array
    {
        foreach ($rows as &$row) {
            $pendencias = (int) ($row['documentos_pendentes'] ?? 0);
            $indisponivel = in_array((string) ($row['status'] ?? ''), ['manutencao', 'em_manutencao', 'baixado'], true);

            $row['situacao_publicacao'] = $pendencias > 0
                ? 'pendencia_documental'
                : ($indisponivel ? 'restricao_operacional' : 'regular');
        }
        unset($row);

        usort($rows, static function (array $left, array $right): int {
            $priority = ['pendencia_documental' => 0, 'restricao_operacional' => 1, 'regular' => 2];
            $leftPriority = $priority[(string) ($left['situacao_publicacao'] ?? 'regular')] ?? 9;
            $rightPriority = $priority[(string) ($right['situacao_publicacao'] ?? 'regular')] ?? 9;

            return [$leftPriority, (string) ($left['secretaria_lotada'] ?? ''), (string) ($left['placa'] ?? '')]
                <=> [$rightPriority, (string) ($right['secretaria_lotada'] ?? ''), (string) ($right['placa'] ?? '')];
        });

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
