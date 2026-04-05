<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class AbastecimentoModel
{
    public function getAll(?int $veiculoId = null, ?string $dataInicio = null, ?string $dataFim = null): array
    {
        global $pdo;

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

        $sql = 'SELECT a.*, v.placa, v.modelo, m.nome AS motorista_nome, m.secretaria, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
                FROM abastecimentos a
                INNER JOIN veiculos v ON v.id = a.veiculo_id
                INNER JOIN motoristas m ON m.id = a.motorista_id
                LEFT JOIN parceiros_operacionais p ON p.id = a.parceiro_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY a.data_abastecimento DESC, a.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'SELECT a.*, v.placa, v.modelo, m.nome AS motorista_nome, m.secretaria, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM abastecimentos a
             INNER JOIN veiculos v ON v.id = a.veiculo_id
             INNER JOIN motoristas m ON m.id = a.motorista_id
             LEFT JOIN parceiros_operacionais p ON p.id = a.parceiro_id
             WHERE a.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }

    public function create(array $data): int
    {
        global $pdo;

        $stmt = $pdo->prepare(
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

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        global $pdo;

        $stmt = $pdo->prepare(
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
        global $pdo;

        $limit = max(1, $limit);
        $stmt = $pdo->query(
            'SELECT a.*, v.placa, v.modelo, m.nome AS motorista_nome, m.secretaria, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM abastecimentos a
             INNER JOIN veiculos v ON v.id = a.veiculo_id
             INNER JOIN motoristas m ON m.id = a.motorista_id
             LEFT JOIN parceiros_operacionais p ON p.id = a.parceiro_id
             ORDER BY a.data_abastecimento DESC, a.id DESC
             LIMIT ' . $limit
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totalValorPeriodo(?string $dataInicio = null, ?string $dataFim = null): float
    {
        global $pdo;

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

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (float) $stmt->fetchColumn();
    }
}
