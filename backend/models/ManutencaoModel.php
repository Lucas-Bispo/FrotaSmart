<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class ManutencaoModel
{
    public function getAll(): array
    {
        global $pdo;
        $stmt = $pdo->query(
            'SELECT m.*, v.placa, v.modelo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             ORDER BY m.data_abertura DESC, m.id DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'SELECT m.*, v.placa, v.modelo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             WHERE m.id = :id
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
            'INSERT INTO manutencoes (
                veiculo_id,
                data_abertura,
                data_conclusao,
                data,
                tipo,
                status,
                fornecedor,
                custo_estimado,
                custo_final,
                custo,
                descricao,
                observacoes
             ) VALUES (
                :veiculo_id,
                :data_abertura,
                :data_conclusao,
                :data_legada,
                :tipo,
                :status,
                :fornecedor,
                :custo_estimado,
                :custo_final,
                :custo_legado,
                :descricao,
                :observacoes
             )'
        );

        $stmt->execute([
            ':veiculo_id' => $data['veiculo_id'],
            ':data_abertura' => $data['data_abertura'],
            ':data_conclusao' => $data['data_conclusao'],
            ':data_legada' => $data['data_abertura'],
            ':tipo' => $data['tipo'],
            ':status' => $data['status'],
            ':fornecedor' => $data['fornecedor'],
            ':custo_estimado' => $data['custo_estimado'],
            ':custo_final' => $data['custo_final'],
            ':custo_legado' => $this->legacyCost($data),
            ':descricao' => $data['descricao'],
            ':observacoes' => $data['observacoes'],
        ]);

        $id = (int) $pdo->lastInsertId();
        $this->syncVeiculoStatus((int) $data['veiculo_id']);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'UPDATE manutencoes
             SET veiculo_id = :veiculo_id,
                 data_abertura = :data_abertura,
                 data_conclusao = :data_conclusao,
                 data = :data_legada,
                 tipo = :tipo,
                 status = :status,
                 fornecedor = :fornecedor,
                 custo_estimado = :custo_estimado,
                 custo_final = :custo_final,
                 custo = :custo_legado,
                 descricao = :descricao,
                 observacoes = :observacoes
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':veiculo_id' => $data['veiculo_id'],
            ':data_abertura' => $data['data_abertura'],
            ':data_conclusao' => $data['data_conclusao'],
            ':data_legada' => $data['data_abertura'],
            ':tipo' => $data['tipo'],
            ':status' => $data['status'],
            ':fornecedor' => $data['fornecedor'],
            ':custo_estimado' => $data['custo_estimado'],
            ':custo_final' => $data['custo_final'],
            ':custo_legado' => $this->legacyCost($data),
            ':descricao' => $data['descricao'],
            ':observacoes' => $data['observacoes'],
        ]);

        $this->syncVeiculoStatus((int) $data['veiculo_id']);
    }

    public function countAbertas(): int
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM manutencoes WHERE status IN ('aberta', 'em_andamento')");

        return (int) $stmt->fetchColumn();
    }

    public function countByVeiculoOpen(int $veiculoId): int
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM manutencoes WHERE veiculo_id = :veiculo_id AND status IN ('aberta', 'em_andamento')");
        $stmt->execute([':veiculo_id' => $veiculoId]);

        return (int) $stmt->fetchColumn();
    }

    public function syncVeiculoStatus(int $veiculoId): void
    {
        global $pdo;
        $status = $this->countByVeiculoOpen($veiculoId) > 0 ? 'manutencao' : 'ativo';
        $stmt = $pdo->prepare('UPDATE veiculos SET status = :status WHERE id = :id');
        $stmt->execute([
            ':status' => $status,
            ':id' => $veiculoId,
        ]);
    }

    private function legacyCost(array $data): float
    {
        $final = (float) ($data['custo_final'] ?? 0);
        $estimated = (float) ($data['custo_estimado'] ?? 0);

        return $final > 0 ? $final : $estimated;
    }
}
