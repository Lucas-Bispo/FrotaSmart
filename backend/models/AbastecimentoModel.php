<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class AbastecimentoModel
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
    }

    public function getAll(?int $veiculoId = null, ?string $dataInicio = null, ?string $dataFim = null): array
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

        $sql = 'SELECT a.*, v.placa, v.modelo, v.secretaria_lotada AS veiculo_secretaria_lotada, m.nome AS motorista_nome, m.secretaria, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
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

    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT a.*, v.placa, v.modelo, v.secretaria_lotada AS veiculo_secretaria_lotada, m.nome AS motorista_nome, m.secretaria, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM abastecimentos a
             INNER JOIN veiculos v ON v.id = a.veiculo_id
             INNER JOIN motoristas m ON m.id = a.motorista_id
             LEFT JOIN parceiros_operacionais p ON p.id = a.parceiro_id
             WHERE a.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        $rows = $this->getAll((int) $result['veiculo_id']);

        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return $result;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO abastecimentos (
                veiculo_id,
                motorista_id,
                parceiro_id,
                data_abastecimento,
                posto,
                tipo_combustivel,
                litros,
                valor_total,
                km_atual,
                observacoes
             ) VALUES (
                :veiculo_id,
                :motorista_id,
                :parceiro_id,
                :data_abastecimento,
                :posto,
                :tipo_combustivel,
                :litros,
                :valor_total,
                :km_atual,
                :observacoes
             )'
        );

        $stmt->execute([
            ':veiculo_id' => $data['veiculo_id'],
            ':motorista_id' => $data['motorista_id'],
            ':parceiro_id' => $data['parceiro_id'] ?? null,
            ':data_abastecimento' => $data['data_abastecimento'],
            ':posto' => $data['posto'],
            ':tipo_combustivel' => $data['tipo_combustivel'],
            ':litros' => $data['litros'],
            ':valor_total' => $data['valor_total'],
            ':km_atual' => $data['km_atual'],
            ':observacoes' => $data['observacoes'],
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE abastecimentos
             SET veiculo_id = :veiculo_id,
                 motorista_id = :motorista_id,
                 parceiro_id = :parceiro_id,
                 data_abastecimento = :data_abastecimento,
                 posto = :posto,
                 tipo_combustivel = :tipo_combustivel,
                 litros = :litros,
                 valor_total = :valor_total,
                 km_atual = :km_atual,
                 observacoes = :observacoes
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':veiculo_id' => $data['veiculo_id'],
            ':motorista_id' => $data['motorista_id'],
            ':parceiro_id' => $data['parceiro_id'] ?? null,
            ':data_abastecimento' => $data['data_abastecimento'],
            ':posto' => $data['posto'],
            ':tipo_combustivel' => $data['tipo_combustivel'],
            ':litros' => $data['litros'],
            ':valor_total' => $data['valor_total'],
            ':km_atual' => $data['km_atual'],
            ':observacoes' => $data['observacoes'],
        ]);
    }

    public function getRecent(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $stmt = $this->connection->query(
            'SELECT a.*, v.placa, v.modelo, v.secretaria_lotada AS veiculo_secretaria_lotada, m.nome AS motorista_nome, m.secretaria, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM abastecimentos a
             INNER JOIN veiculos v ON v.id = a.veiculo_id
             INNER JOIN motoristas m ON m.id = a.motorista_id
             LEFT JOIN parceiros_operacionais p ON p.id = a.parceiro_id
             ORDER BY a.data_abastecimento DESC, a.id DESC
             LIMIT ' . $limit
        );

        return $this->enrichRowsWithAnalytics($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function totalValorPeriodo(?string $dataInicio = null, ?string $dataFim = null): float
    {
        $conditions = [];
        $params = [];

        if ($dataInicio !== null && $dataInicio !== '') {
            $conditions[] = 'data_abastecimento >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== null && $dataFim !== '') {
            $conditions[] = 'data_abastecimento <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        $sql = 'SELECT COALESCE(SUM(valor_total), 0) FROM abastecimentos';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return (float) $stmt->fetchColumn();
    }

    public function getConsumptionSummary(?string $dataInicio = null, ?string $dataFim = null): array
    {
        $rows = $this->getAll(null, $dataInicio, $dataFim);
        $alertas = array_values(array_filter(
            $rows,
            static fn (array $row): bool => ($row['anomalia_status'] ?? 'normal') !== 'normal'
        ));
        $consumos = array_values(array_filter(
            $rows,
            static fn (array $row): bool => ($row['consumo_km_l'] ?? null) !== null
        ));

        $mediaConsumo = 0.0;
        if ($consumos !== []) {
            $mediaConsumo = array_sum(array_column($consumos, 'consumo_km_l')) / count($consumos);
        }

        return [
            'media_consumo_km_l' => round($mediaConsumo, 2),
            'total_alertas' => count($alertas),
            'alertas_criticos' => count(array_filter($alertas, static fn (array $row): bool => ($row['anomalia_status'] ?? '') === 'critico')),
            'alertas_atencao' => count(array_filter($alertas, static fn (array $row): bool => ($row['anomalia_status'] ?? '') === 'atencao')),
            'top_alertas' => array_slice($alertas, 0, 5),
        ];
    }

    public function getVehicleEfficiencyRanking(int $limit = 5, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        $rows = $this->getAll(null, $dataInicio, $dataFim);
        $grouped = [];

        foreach ($rows as $row) {
            if (($row['consumo_km_l'] ?? null) === null) {
                continue;
            }

            $key = (string) $row['veiculo_id'];
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'veiculo_id' => (int) $row['veiculo_id'],
                    'placa' => $row['placa'],
                    'modelo' => $row['modelo'],
                    'secretaria' => $row['secretaria'],
                    'leituras' => 0,
                    'media_consumo_km_l' => 0.0,
                ];
            }

            $grouped[$key]['leituras']++;
            $grouped[$key]['media_consumo_km_l'] += (float) $row['consumo_km_l'];
        }

        foreach ($grouped as &$item) {
            if ($item['leituras'] > 0) {
                $item['media_consumo_km_l'] = round($item['media_consumo_km_l'] / $item['leituras'], 2);
            }
        }
        unset($item);

        usort($grouped, static fn (array $a, array $b): int => $b['media_consumo_km_l'] <=> $a['media_consumo_km_l']);

        return array_slice($grouped, 0, max(1, $limit));
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
                    $row['variacao_consumo_percentual'] = $this->percentageVariation((float) $anterior['consumo_km_l'], (float) $row['consumo_km_l']);
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

    private function resolveLegacyConnection(): PDO
    {
        global $pdo;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        throw new RuntimeException('Conexao PDO indisponivel para AbastecimentoModel.');
    }
}
