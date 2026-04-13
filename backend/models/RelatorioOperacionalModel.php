<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AbastecimentoModel.php';
require_once __DIR__ . '/ManutencaoModel.php';

final class RelatorioOperacionalModel
{
    private PDO $connection;
    private \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService $queries;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
        $this->queries = new \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService($this->connection);
    }

    public function getSecretarias(): array
    {
        return $this->queries->fetchSecretarias();
    }

    public function getVeiculos(): array
    {
        return $this->queries->fetchVeiculos();
    }

    public function getAbastecimentoReport(array $filters): array
    {
        $abastecimentoModel = new AbastecimentoModel();
        $rows = $abastecimentoModel->getAll(
            $this->normalizeOptionalInt($filters['veiculo_id'] ?? null),
            $this->normalizeOptionalString($filters['data_inicio'] ?? null),
            $this->normalizeOptionalString($filters['data_fim'] ?? null)
        );

        $secretaria = $this->normalizeOptionalString($filters['secretaria'] ?? null);
        $status = $this->normalizeOptionalString($filters['status'] ?? null);

        return array_values(array_filter($rows, static function (array $row) use ($secretaria, $status): bool {
            if ($secretaria !== null && (string) ($row['secretaria'] ?? '') !== $secretaria) {
                return false;
            }

            if ($status !== null && (string) ($row['anomalia_status'] ?? 'normal') !== $status) {
                return false;
            }

            return true;
        }));
    }

    public function getManutencaoReport(array $filters): array
    {
        return $this->queries->fetchManutencaoReport($filters);
    }

    public function getViagemReport(array $filters): array
    {
        $rows = $this->queries->fetchViagemReport($filters);

        foreach ($rows as &$row) {
            $kmSaida = (int) ($row['km_saida'] ?? 0);
            $kmChegada = isset($row['km_chegada']) ? (int) $row['km_chegada'] : null;
            $row['km_percorrido'] = ($kmChegada !== null && $kmChegada >= $kmSaida) ? $kmChegada - $kmSaida : null;
        }
        unset($row);

        return $rows;
    }

    public function getDisponibilidadeReport(array $filters): array
    {
        $rows = $this->queries->fetchDisponibilidadeReport($filters);

        foreach ($rows as &$row) {
            $row['situacao_disponibilidade'] = ! empty($row['deleted_at'])
                ? 'arquivado'
                : ((string) $row['status'] === 'manutencao' ? 'indisponivel_manutencao' : 'disponivel_operacao');
        }
        unset($row);

        return $rows;
    }

    public function getResumo(array $filters): array
    {
        $abastecimentos = $this->getAbastecimentoReport($filters);
        $manutencoes = $this->getManutencaoReport($filters);
        $viagens = $this->getViagemReport($filters);
        $disponibilidade = $this->getDisponibilidadeReport($filters);

        return [
            'abastecimentos' => count($abastecimentos),
            'gasto_abastecimento' => round(array_sum(array_map(static fn (array $row): float => (float) ($row['valor_total'] ?? 0), $abastecimentos)), 2),
            'manutencoes' => count($manutencoes),
            'custo_manutencao' => round(array_sum(array_map(static fn (array $row): float => (float) (($row['custo_final'] ?? 0) > 0 ? $row['custo_final'] : ($row['custo_estimado'] ?? 0)), $manutencoes)), 2),
            'viagens' => count($viagens),
            'km_viagens' => array_sum(array_map(static fn (array $row): int => (int) ($row['km_percorrido'] ?? 0), $viagens)),
            'veiculos_disponiveis' => count(array_filter($disponibilidade, static fn (array $row): bool => ($row['situacao_disponibilidade'] ?? '') === 'disponivel_operacao')),
        ];
    }

    public function getExecutiveSummaryBySecretaria(?string $dataInicio = null, ?string $dataFim = null): array
    {
        $summaries = [];

        foreach ($this->getSecretarias() as $secretaria) {
            $normalized = $this->normalizeSecretariaName($secretaria);
            $summaries[$normalized] = $this->emptySecretariaSummary($normalized);
        }

        foreach ($this->queries->fetchFleetSummaryBySecretaria() as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['frota_ativa'] = (int) ($row['frota_ativa'] ?? 0);
            $summaries[$secretaria]['frota_arquivada'] = (int) ($row['frota_arquivada'] ?? 0);
            $summaries[$secretaria]['frota_operacao'] = (int) ($row['frota_operacao'] ?? 0);
            $summaries[$secretaria]['frota_manutencao'] = (int) ($row['frota_manutencao'] ?? 0);
        }

        foreach ($this->queries->fetchMotoristasAtivosBySecretaria() as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['motoristas_ativos'] = (int) ($row['motoristas_ativos'] ?? 0);
        }

        foreach ($this->queries->fetchViagensSummaryBySecretaria($dataInicio, $dataFim) as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['viagens_periodo'] = (int) ($row['viagens_periodo'] ?? 0);
            $summaries[$secretaria]['km_viagens_periodo'] = (int) ($row['km_viagens_periodo'] ?? 0);
        }

        $abastecimentoModel = new AbastecimentoModel();
        foreach ($abastecimentoModel->getAll(null, $dataInicio, $dataFim) as $row) {
            $secretaria = $this->normalizeSecretariaName(
                $row['veiculo_secretaria_lotada'] ?? $row['secretaria'] ?? null
            );
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['abastecimentos_periodo']++;
            $summaries[$secretaria]['gasto_abastecimento_periodo'] += (float) ($row['valor_total'] ?? 0);

            if (($row['anomalia_status'] ?? 'normal') !== 'normal') {
                $summaries[$secretaria]['alertas_abastecimento']++;
            }
        }

        $manutencaoModel = new ManutencaoModel();
        foreach ($manutencaoModel->getAll() as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria_lotada'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);

            if (in_array((string) ($row['status'] ?? ''), ['aberta', 'em_andamento'], true)) {
                $summaries[$secretaria]['manutencoes_abertas']++;
            }

            if ($this->isWithinDateRange((string) ($row['data_abertura'] ?? ''), $dataInicio, $dataFim)) {
                $summaries[$secretaria]['manutencoes_periodo']++;
                $summaries[$secretaria]['custo_manutencao_periodo'] += $this->resolveMaintenanceCost($row);
            }
        }

        foreach ($manutencaoModel->getPreventiveAlerts() as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria_lotada'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);

            if (($row['preventiva_alerta_status'] ?? '') === 'vencida') {
                $summaries[$secretaria]['preventivas_vencidas']++;
            }

            if (($row['preventiva_alerta_status'] ?? '') === 'proxima') {
                $summaries[$secretaria]['preventivas_proximas']++;
            }
        }

        foreach ($summaries as &$summary) {
            $summary['gasto_abastecimento_periodo'] = round((float) $summary['gasto_abastecimento_periodo'], 2);
            $summary['custo_manutencao_periodo'] = round((float) $summary['custo_manutencao_periodo'], 2);
            $summary['custo_total_periodo'] = round(
                (float) $summary['gasto_abastecimento_periodo'] + (float) $summary['custo_manutencao_periodo'],
                2
            );
            $summary['disponibilidade_percentual'] = $summary['frota_ativa'] > 0
                ? round(($summary['frota_operacao'] / $summary['frota_ativa']) * 100, 1)
                : null;
            $summary['alertas_total'] = $summary['preventivas_vencidas']
                + $summary['preventivas_proximas']
                + $summary['manutencoes_abertas']
                + $summary['alertas_abastecimento'];
        }
        unset($summary);

        usort($summaries, static function (array $a, array $b): int {
            return [$b['custo_total_periodo'], $b['frota_ativa'], $a['secretaria']]
                <=> [$a['custo_total_periodo'], $a['frota_ativa'], $b['secretaria']];
        });

        return array_values($summaries);
    }

    public function getExecutiveSummaryByVeiculo(?string $dataInicio = null, ?string $dataFim = null, int $limit = 8): array
    {
        $summaries = [];

        foreach ($this->getVeiculos() as $veiculo) {
            $veiculoId = (int) ($veiculo['id'] ?? 0);

            if ($veiculoId <= 0) {
                continue;
            }

            $summaries[$veiculoId] = [
                'veiculo_id' => $veiculoId,
                'placa' => (string) ($veiculo['placa'] ?? ''),
                'modelo' => (string) ($veiculo['modelo'] ?? ''),
                'secretaria_lotada' => $this->normalizeSecretariaName($veiculo['secretaria_lotada'] ?? null),
                'status' => (string) ($veiculo['status'] ?? ''),
                'deleted_at' => $veiculo['deleted_at'] ?? null,
                'viagens_periodo' => 0,
                'km_viagens_periodo' => 0,
                'abastecimentos_periodo' => 0,
                'gasto_abastecimento_periodo' => 0.0,
                'alertas_abastecimento' => 0,
                'consumo_medio_km_l' => null,
                'manutencoes_abertas' => 0,
                'manutencoes_periodo' => 0,
                'custo_manutencao_periodo' => 0.0,
                'preventiva_status' => 'sem_plano',
                'preventiva_resumo' => 'Sem alerta preventivo no momento.',
                'total_alertas' => 0,
                'custo_total_periodo' => 0.0,
            ];
        }

        foreach ($this->queries->fetchViagensSummaryByVeiculo($dataInicio, $dataFim) as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            $summaries[$veiculoId]['viagens_periodo'] = (int) ($row['viagens_periodo'] ?? 0);
            $summaries[$veiculoId]['km_viagens_periodo'] = (int) ($row['km_viagens_periodo'] ?? 0);
        }

        $abastecimentoModel = new AbastecimentoModel();
        foreach ($abastecimentoModel->getAll(null, $dataInicio, $dataFim) as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            $summaries[$veiculoId]['abastecimentos_periodo']++;
            $summaries[$veiculoId]['gasto_abastecimento_periodo'] += (float) ($row['valor_total'] ?? 0);

            if (($row['anomalia_status'] ?? 'normal') !== 'normal') {
                $summaries[$veiculoId]['alertas_abastecimento']++;
            }

            if (($row['consumo_km_l'] ?? null) !== null) {
                $summaries[$veiculoId]['_consumo_soma'] = ($summaries[$veiculoId]['_consumo_soma'] ?? 0.0) + (float) $row['consumo_km_l'];
                $summaries[$veiculoId]['_consumo_leituras'] = ($summaries[$veiculoId]['_consumo_leituras'] ?? 0) + 1;
            }
        }

        $manutencaoModel = new ManutencaoModel();
        foreach ($manutencaoModel->getAll() as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            if (in_array((string) ($row['status'] ?? ''), ['aberta', 'em_andamento'], true)) {
                $summaries[$veiculoId]['manutencoes_abertas']++;
            }

            if ($this->isWithinDateRange((string) ($row['data_abertura'] ?? ''), $dataInicio, $dataFim)) {
                $summaries[$veiculoId]['manutencoes_periodo']++;
                $summaries[$veiculoId]['custo_manutencao_periodo'] += $this->resolveMaintenanceCost($row);
            }
        }

        foreach ($manutencaoModel->getPreventiveAlerts() as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            $novoStatus = (string) ($row['preventiva_alerta_status'] ?? 'sem_plano');
            $statusAtual = (string) ($summaries[$veiculoId]['preventiva_status'] ?? 'sem_plano');

            if ($this->preventiveStatusSeverity($novoStatus) > $this->preventiveStatusSeverity($statusAtual)) {
                $summaries[$veiculoId]['preventiva_status'] = $novoStatus;
                $summaries[$veiculoId]['preventiva_resumo'] = (string) ($row['preventiva_alerta_resumo'] ?? 'Alerta preventivo identificado.');
            }
        }

        foreach ($summaries as &$summary) {
            $leituras = (int) ($summary['_consumo_leituras'] ?? 0);
            $soma = (float) ($summary['_consumo_soma'] ?? 0);
            $summary['consumo_medio_km_l'] = $leituras > 0 ? round($soma / $leituras, 2) : null;
            $summary['gasto_abastecimento_periodo'] = round((float) $summary['gasto_abastecimento_periodo'], 2);
            $summary['custo_manutencao_periodo'] = round((float) $summary['custo_manutencao_periodo'], 2);
            $summary['custo_total_periodo'] = round(
                (float) $summary['gasto_abastecimento_periodo'] + (float) $summary['custo_manutencao_periodo'],
                2
            );
            $summary['total_alertas'] = $summary['alertas_abastecimento'] + $summary['manutencoes_abertas'];

            if (in_array((string) $summary['preventiva_status'], ['vencida', 'proxima'], true)) {
                $summary['total_alertas']++;
            }

            if (! empty($summary['deleted_at'])) {
                $summary['total_alertas']++;
            }

            unset($summary['_consumo_soma'], $summary['_consumo_leituras']);
        }
        unset($summary);

        usort($summaries, static function (array $a, array $b): int {
            return [
                $b['total_alertas'],
                $b['custo_total_periodo'],
                $b['km_viagens_periodo'],
                $a['placa'],
            ] <=> [
                $a['total_alertas'],
                $a['custo_total_periodo'],
                $a['km_viagens_periodo'],
                $b['placa'],
            ];
        });

        return array_slice(array_values($summaries), 0, max(1, $limit));
    }

    public function getAuditReport(array $filters): array
    {
        return $this->fetchAuditRows($filters);
    }

    public function getAuditSummary(array $filters): array
    {
        $rows = $this->fetchAuditRows($filters);
        $actors = [];
        $exports = 0;
        $blocked = 0;
        $mutations = 0;

        foreach ($rows as $row) {
            $actor = trim((string) ($row['actor'] ?? ''));
            if ($actor !== '') {
                $actors[$actor] = true;
            }

            if (($row['action'] ?? '') === 'export' || ($row['event'] ?? '') === 'relatorio.exported') {
                $exports++;
            }

            if (($row['action'] ?? '') === 'blocked') {
                $blocked++;
            }

            if (in_array((string) ($row['action'] ?? ''), ['create', 'update', 'delete', 'archive', 'restore'], true)) {
                $mutations++;
            }
        }

        return [
            'eventos_total' => count($rows),
            'atores_unicos' => count($actors),
            'exportacoes' => $exports,
            'bloqueios' => $blocked,
            'mutacoes' => $mutations,
        ];
    }

    public function getAuditTargetTypes(): array
    {
        return $this->queries->fetchAuditTargetTypes();
    }

    public function exportCsv(string $report, array $filters): string
    {
        $rows = match ($report) {
            'abastecimentos' => $this->getAbastecimentoReport($filters),
            'manutencoes' => $this->getManutencaoReport($filters),
            'viagens' => $this->getViagemReport($filters),
            'disponibilidade' => $this->getDisponibilidadeReport($filters),
            'auditoria' => $this->getAuditReport($filters),
            default => [],
        };

        $stream = fopen('php://temp', 'r+');

        if ($rows === []) {
            fputcsv($stream, ['sem_dados']);
        } else {
            fputcsv($stream, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $content;
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

    private function normalizeSecretariaName(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : 'Secretaria nao informada';
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySecretariaSummary(string $secretaria): array
    {
        return [
            'secretaria' => $secretaria,
            'frota_ativa' => 0,
            'frota_arquivada' => 0,
            'frota_operacao' => 0,
            'frota_manutencao' => 0,
            'motoristas_ativos' => 0,
            'viagens_periodo' => 0,
            'km_viagens_periodo' => 0,
            'abastecimentos_periodo' => 0,
            'gasto_abastecimento_periodo' => 0.0,
            'alertas_abastecimento' => 0,
            'manutencoes_periodo' => 0,
            'manutencoes_abertas' => 0,
            'custo_manutencao_periodo' => 0.0,
            'preventivas_vencidas' => 0,
            'preventivas_proximas' => 0,
            'custo_total_periodo' => 0.0,
            'disponibilidade_percentual' => null,
            'alertas_total' => 0,
        ];
    }

    private function isWithinDateRange(string $date, ?string $dataInicio, ?string $dataFim): bool
    {
        if ($date === '') {
            return false;
        }

        if ($dataInicio !== null && $dataInicio !== '' && $date < $dataInicio) {
            return false;
        }

        if ($dataFim !== null && $dataFim !== '' && $date > $dataFim) {
            return false;
        }

        return true;
    }

    private function resolveMaintenanceCost(array $row): float
    {
        $final = (float) ($row['custo_final'] ?? 0);
        $estimado = (float) ($row['custo_estimado'] ?? 0);

        return $final > 0 ? $final : $estimado;
    }

    private function preventiveStatusSeverity(string $status): int
    {
        return match ($status) {
            'vencida' => 3,
            'proxima' => 2,
            'em_dia' => 1,
            default => 0,
        };
    }

    private function fetchAuditRows(array $filters): array
    {
        $rows = $this->queries->fetchAuditRows($filters);

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

    private function resolveLegacyConnection(): PDO
    {
        global $pdo;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        throw new RuntimeException('Conexao PDO indisponivel para RelatorioOperacionalModel.');
    }
}
