<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\ReadModels;

use PDO;
use RuntimeException;

final class AbastecimentoReadModel
{
    public function __construct(
        private readonly PDO $connection
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchAll(?int $veiculoId = null, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $conditions = [];
        $params = [];

        if ($veiculoId !== null && $veiculoId > 0) {
            $conditions[] = 'a.veiculo_id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if ($dataInicio !== null && $dataInicio !== '') {
            $conditions[] = 'a.data_abastecimento >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== null && $dataFim !== '') {
            $conditions[] = 'a.data_abastecimento <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        $sql = 'SELECT
                    a.*,
                    v.placa,
                    v.modelo,
                    v.secretaria_lotada AS veiculo_secretaria_lotada,
                    m.nome AS motorista_nome,
                    m.secretaria,
                    p.nome_fantasia AS parceiro_nome,
                    p.tipo AS parceiro_tipo
                FROM abastecimentos a
                INNER JOIN veiculos v ON v.id = a.veiculo_id
                INNER JOIN motoristas m ON m.id = a.motorista_id
                LEFT JOIN parceiros_operacionais p ON p.id = a.parceiro_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY a.data_abastecimento DESC, a.id DESC';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $this->enrichRowsWithAnalytics($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function enrichRowsWithAnalytics(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $ordered = array_reverse($rows);
        $previousByVehicle = [];
        $vehicleConsumptions = [];
        $enrichedOrdered = [];

        foreach ($ordered as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            $kmAtual = (int) ($row['km_atual'] ?? 0);
            $litros = (float) ($row['litros'] ?? 0);
            $valorTotal = (float) ($row['valor_total'] ?? 0);
            $anterior = $previousByVehicle[$veiculoId] ?? null;

            $row['consumo_km_l'] = null;
            $row['km_percorrido_desde_anterior'] = null;
            $row['variacao_litros_percentual'] = null;
            $row['variacao_valor_percentual'] = null;
            $row['variacao_consumo_percentual'] = null;
            $row['custo_por_litro'] = $litros > 0 ? round($valorTotal / $litros, 2) : null;
            $row['custo_por_km'] = null;
            $row['anomalia_status'] = 'normal';
            $row['anomalia_resumo'] = null;

            if (is_array($anterior)) {
                $kmAnterior = (int) ($anterior['km_atual'] ?? 0);
                $litrosAnterior = (float) ($anterior['litros'] ?? 0);
                $valorAnterior = (float) ($anterior['valor_total'] ?? 0);
                $kmPercorrido = $kmAtual - $kmAnterior;

                if ($kmPercorrido > 0) {
                    $consumo = $litros > 0 ? $kmPercorrido / $litros : null;
                    $row['km_percorrido_desde_anterior'] = $kmPercorrido;
                    $row['consumo_km_l'] = $consumo !== null ? round($consumo, 2) : null;
                    $row['custo_por_km'] = $valorTotal > 0 ? round($valorTotal / $kmPercorrido, 2) : null;

                    if ($row['consumo_km_l'] !== null) {
                        $vehicleConsumptions[$veiculoId] ??= [];
                        $vehicleConsumptions[$veiculoId][] = $row['consumo_km_l'];
                    }
                }

                $row['variacao_litros_percentual'] = $this->percentageVariation($litrosAnterior, $litros);
                $row['variacao_valor_percentual'] = $this->percentageVariation($valorAnterior, $valorTotal);

                if (($anterior['consumo_km_l'] ?? null) !== null && $row['consumo_km_l'] !== null) {
                    $row['variacao_consumo_percentual'] = $this->percentageVariation(
                        (float) $anterior['consumo_km_l'],
                        (float) $row['consumo_km_l']
                    );
                }
            }

            $previousByVehicle[$veiculoId] = $row;
            $enrichedOrdered[] = $row;
        }

        $consumptionAverages = [];
        foreach ($vehicleConsumptions as $veiculoId => $consumos) {
            if ($consumos === []) {
                continue;
            }

            $consumptionAverages[$veiculoId] = array_sum($consumos) / count($consumos);
        }

        foreach ($enrichedOrdered as &$row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            $mediaVeiculo = $consumptionAverages[$veiculoId] ?? null;
            $row['media_consumo_veiculo_km_l'] = $mediaVeiculo !== null ? round($mediaVeiculo, 2) : null;

            [$status, $resumo] = $this->detectAnomaly($row, $mediaVeiculo);
            $row['anomalia_status'] = $status;
            $row['anomalia_resumo'] = $resumo;
        }
        unset($row);

        return array_reverse($enrichedOrdered);
    }

    private function percentageVariation(float $base, float $current): ?float
    {
        if ($base <= 0) {
            return null;
        }

        return round((($current - $base) / $base) * 100, 2);
    }

    /**
     * @param array<string, mixed> $row
     * @return array{0:string,1:?string}
     */
    private function detectAnomaly(array $row, ?float $mediaVeiculo): array
    {
        $motivos = [];
        $critico = false;
        $atencao = false;

        $kmPercorrido = $row['km_percorrido_desde_anterior'] ?? null;
        $litros = (float) ($row['litros'] ?? 0);
        $custoPorLitro = $row['custo_por_litro'] ?? null;
        $variacaoLitros = $row['variacao_litros_percentual'] ?? null;
        $variacaoValor = $row['variacao_valor_percentual'] ?? null;
        $consumo = $row['consumo_km_l'] ?? null;

        if ($kmPercorrido !== null && $kmPercorrido <= 0) {
            $critico = true;
            $motivos[] = 'KM nao avancou desde o ultimo abastecimento.';
        }

        if ($kmPercorrido !== null && $litros > 0 && $kmPercorrido < 20 && $litros >= 20) {
            $critico = true;
            $motivos[] = 'Litros altos para distancia muito curta.';
        }

        if ($variacaoLitros !== null && $variacaoLitros >= 35) {
            $atencao = true;
            $motivos[] = 'Litros subiram ' . number_format($variacaoLitros, 2, ',', '.') . '% frente ao abastecimento anterior.';
        }

        if ($variacaoValor !== null && $variacaoValor >= 35) {
            $atencao = true;
            $motivos[] = 'Valor total subiu ' . number_format($variacaoValor, 2, ',', '.') . '% frente ao abastecimento anterior.';
        }

        if ($custoPorLitro !== null && $custoPorLitro >= 9.5) {
            $atencao = true;
            $motivos[] = 'Custo por litro acima da faixa esperada.';
        }

        if ($mediaVeiculo !== null && $consumo !== null && $mediaVeiculo > 0) {
            if ($consumo <= ($mediaVeiculo * 0.6)) {
                $critico = true;
                $motivos[] = 'Consumo caiu muito abaixo da media historica do veiculo.';
            } elseif ($consumo <= ($mediaVeiculo * 0.8)) {
                $atencao = true;
                $motivos[] = 'Consumo abaixo da media historica do veiculo.';
            }
        }

        if ($critico) {
            return ['critico', implode(' ', $motivos)];
        }

        if ($atencao) {
            return ['atencao', implode(' ', $motivos)];
        }

        return ['normal', null];
    }
}
