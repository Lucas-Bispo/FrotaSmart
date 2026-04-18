<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class ChecklistOperacionalModel
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
    }

    /**
     * @param array{tipo?:?string,status?:?string,secretaria?:?string} $filters
     * @return list<array<string, mixed>>
     */
    public function listByFilters(array $filters = []): array
    {
        $tipo = $filters['tipo'] ?? null;
        $status = $filters['status'] ?? null;
        $secretaria = $filters['secretaria'] ?? null;

        $conditions = [];
        $params = [];

        if ($tipo !== null && $tipo !== '') {
            $conditions[] = 'c.tipo = :tipo';
            $params[':tipo'] = $tipo;
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 'c.status_conformidade = :status';
            $params[':status'] = $status;
        }

        if ($secretaria !== null && $secretaria !== '') {
            $conditions[] = 'c.secretaria = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        $sql = 'SELECT
                    c.*,
                    v.placa,
                    v.modelo,
                    m.nome AS motorista_nome,
                    vi.destino AS viagem_destino,
                    vi.data_saida AS viagem_data_saida
                FROM checklists_operacionais c
                INNER JOIN veiculos v ON v.id = c.veiculo_id
                INNER JOIN motoristas m ON m.id = c.motorista_id
                LEFT JOIN viagens vi ON vi.id = c.viagem_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY c.realizado_em DESC, c.id DESC';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT *
             FROM checklists_operacionais
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO checklists_operacionais (
                tipo,
                viagem_id,
                veiculo_id,
                motorista_id,
                secretaria,
                responsavel_operacao,
                status_conformidade,
                aceite_responsavel,
                realizado_em,
                itens_json,
                evidencias_json,
                nao_conformidades,
                evidencia_referencia,
                observacoes
            ) VALUES (
                :tipo,
                :viagem_id,
                :veiculo_id,
                :motorista_id,
                :secretaria,
                :responsavel_operacao,
                :status_conformidade,
                :aceite_responsavel,
                :realizado_em,
                :itens_json,
                :evidencias_json,
                :nao_conformidades,
                :evidencia_referencia,
                :observacoes
            )'
        );

        $stmt->execute([
            ':tipo' => $data['tipo'],
            ':viagem_id' => $data['viagem_id'],
            ':veiculo_id' => $data['veiculo_id'],
            ':motorista_id' => $data['motorista_id'],
            ':secretaria' => $data['secretaria'],
            ':responsavel_operacao' => $data['responsavel_operacao'],
            ':status_conformidade' => $data['status_conformidade'],
            ':aceite_responsavel' => $data['aceite_responsavel'],
            ':realizado_em' => $data['realizado_em'],
            ':itens_json' => $data['itens_json'],
            ':evidencias_json' => $data['evidencias_json'],
            ':nao_conformidades' => $data['nao_conformidades'],
            ':evidencia_referencia' => $data['evidencia_referencia'],
            ':observacoes' => $data['observacoes'],
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE checklists_operacionais
             SET tipo = :tipo,
                 viagem_id = :viagem_id,
                 veiculo_id = :veiculo_id,
                 motorista_id = :motorista_id,
                 secretaria = :secretaria,
                 responsavel_operacao = :responsavel_operacao,
                 status_conformidade = :status_conformidade,
                 aceite_responsavel = :aceite_responsavel,
                 realizado_em = :realizado_em,
                 itens_json = :itens_json,
                 evidencias_json = :evidencias_json,
                 nao_conformidades = :nao_conformidades,
                 evidencia_referencia = :evidencia_referencia,
                 observacoes = :observacoes
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':tipo' => $data['tipo'],
            ':viagem_id' => $data['viagem_id'],
            ':veiculo_id' => $data['veiculo_id'],
            ':motorista_id' => $data['motorista_id'],
            ':secretaria' => $data['secretaria'],
            ':responsavel_operacao' => $data['responsavel_operacao'],
            ':status_conformidade' => $data['status_conformidade'],
            ':aceite_responsavel' => $data['aceite_responsavel'],
            ':realizado_em' => $data['realizado_em'],
            ':itens_json' => $data['itens_json'],
            ':evidencias_json' => $data['evidencias_json'],
            ':nao_conformidades' => $data['nao_conformidades'],
            ':evidencia_referencia' => $data['evidencia_referencia'],
            ':observacoes' => $data['observacoes'],
        ]);
    }

    private function resolveLegacyConnection(): PDO
    {
        return database_connection();
    }
}
