<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Infrastructure\ReadModels\AbastecimentoReadModel;
use FrotaSmart\Infrastructure\ReadModels\ManutencaoReadModel;
use FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService;

final class RelatorioExecutiveSummaryService
{
    public function __construct(
        private readonly RelatorioOperacionalQueryService $queries,
        private readonly AbastecimentoReadModel $abastecimentos,
        private readonly ManutencaoReadModel $manutencoes
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildBySecretaria(?string $dataInicio = null, ?string $dataFim = null): array
    {
        $summaries = [];

        foreach ($this->queries->fetchSecretarias() as $secretaria) {
            $name = $this->normalizeSecretariaName($secretaria);
            $summaries[$name] = $this->emptySecretariaSummary($name);
        }

        foreach ($this->queries->fetchFleetSummaryBySecretaria() as $row) {
            $name = $this->touchSecretariaSummary($summaries, $row['secretaria'] ?? null);
            $summaries[$name]['frota_ativa'] = (int) ($row['frota_ativa'] ?? 0);
            $summaries[$name]['frota_arquivada'] = (int) ($row['frota_arquivada'] ?? 0);
            $summaries[$name]['frota_operacao'] = (int) ($row['frota_operacao'] ?? 0);
            $summaries[$name]['frota_manutencao'] = (int) ($row['frota_manutencao'] ?? 0);
        }

        foreach ($this->queries->fetchMotoristasAtivosBySecretaria() as $row) {
            $name = $this->touchSecretariaSummary($summaries, $row['secretaria'] ?? null);
            $summaries[$name]['motoristas_ativos'] = (int) ($row['motoristas_ativos'] ?? 0);
        }

        foreach ($this->queries->fetchViagensSummaryBySecretaria($dataInicio, $dataFim) as $row) {
            $name = $this->touchSecretariaSummary($summaries, $row['secretaria'] ?? null);
            $summaries[$name]['viagens_periodo'] = (int) ($row['viagens_periodo'] ?? 0);
            $summaries[$name]['km_viagens_periodo'] = (int) ($row['km_viagens_periodo'] ?? 0);
        }

        foreach ($this->abastecimentos->fetchByCriteria([
            'veiculo_id' => null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]) as $row) {
            $name = $this->touchSecretariaSummary(
                $summaries,
                $row['veiculo_secretaria_lotada'] ?? $row['secretaria'] ?? null
            );
            $summaries[$name]['abastecimentos_periodo']++;
            $summaries[$name]['gasto_abastecimento_periodo'] += (float) ($row['valor_total'] ?? 0);

            if (($row['anomalia_status'] ?? 'normal') !== 'normal') {
                $summaries[$name]['alertas_abastecimento']++;
            }
        }

        foreach ($this->manutencoes->fetchAll() as $row) {
            $name = $this->touchSecretariaSummary($summaries, $row['secretaria_lotada'] ?? null);

            if (in_array((string) ($row['status'] ?? ''), ['aberta', 'em_andamento'], true)) {
                $summaries[$name]['manutencoes_abertas']++;
            }

            if ($this->isWithinDateRange((string) ($row['data_abertura'] ?? ''), $dataInicio, $dataFim)) {
                $summaries[$name]['manutencoes_periodo']++;
                $summaries[$name]['custo_manutencao_periodo'] += $this->resolveMaintenanceCost($row);
            }
        }

        foreach ($this->manutencoes->fetchPreventiveAlerts() as $row) {
            $name = $this->touchSecretariaSummary($summaries, $row['secretaria_lotada'] ?? null);

            if (($row['preventiva_alerta_status'] ?? '') === 'vencida') {
                $summaries[$name]['preventivas_vencidas']++;
            }

            if (($row['preventiva_alerta_status'] ?? '') === 'proxima') {
                $summaries[$name]['preventivas_proximas']++;
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

        $summaries = array_values($summaries);
        usort($summaries, static function (array $a, array $b): int {
            return [$b['custo_total_periodo'], $b['frota_ativa'], $a['secretaria']]
                <=> [$a['custo_total_periodo'], $a['frota_ativa'], $b['secretaria']];
        });

        return $summaries;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildByVeiculo(?string $dataInicio = null, ?string $dataFim = null, int $limit = 8): array
    {
        $summaries = [];

        foreach ($this->queries->fetchVeiculos() as $veiculo) {
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

        foreach ($this->abastecimentos->fetchByCriteria([
            'veiculo_id' => null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]) as $row) {
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

        foreach ($this->manutencoes->fetchAll() as $row) {
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

        foreach ($this->manutencoes->fetchPreventiveAlerts() as $row) {
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

        $summaries = array_values($summaries);
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

        return array_slice($summaries, 0, max(1, $limit));
    }

    private function normalizeSecretariaName(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : 'Secretaria nao informada';
    }

    /**
     * @param array<string, array<string, mixed>> $summaries
     */
    private function touchSecretariaSummary(array &$summaries, mixed $secretariaValue): string
    {
        $secretaria = $this->normalizeSecretariaName($secretariaValue);
        $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);

        return $secretaria;
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

    /**
     * @param array<string, mixed> $row
     */
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
}
