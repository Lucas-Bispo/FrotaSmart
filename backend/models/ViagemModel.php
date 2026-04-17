<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class ViagemModel
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
    }

    /**
     * @param array{status?:?string,secretaria?:?string} $filters
     */
    public function listByFilters(array $filters = []): array
    {
        $status = $filters['status'] ?? null;
        $secretaria = $filters['secretaria'] ?? null;
        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $conditions[] = 'v.status = :status';
            $params[':status'] = $status;
        }

        if ($secretaria !== null && $secretaria !== '') {
            $conditions[] = 'v.secretaria = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        $sql = 'SELECT v.*, ve.placa, ve.modelo, m.nome AS motorista_nome
                FROM viagens v
                INNER JOIN veiculos ve ON ve.id = v.veiculo_id
                INNER JOIN motoristas m ON m.id = v.motorista_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY v.data_saida DESC, v.id DESC';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT v.*, ve.placa, ve.modelo, m.nome AS motorista_nome
             FROM viagens v
             INNER JOIN veiculos ve ON ve.id = v.veiculo_id
             INNER JOIN motoristas m ON m.id = v.motorista_id
             WHERE v.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO viagens (
                veiculo_id,
                motorista_id,
                secretaria_id,
                secretaria,
                solicitante,
                origem,
                destino,
                finalidade,
                data_saida,
                data_retorno,
                km_saida,
                km_chegada,
                status,
                observacoes
             ) VALUES (
                :veiculo_id,
                :motorista_id,
                :secretaria_id,
                :secretaria,
                :solicitante,
                :origem,
                :destino,
                :finalidade,
                :data_saida,
                :data_retorno,
                :km_saida,
                :km_chegada,
                :status,
                :observacoes
             )'
        );

        $stmt->execute([
            ':veiculo_id' => $data['veiculo_id'],
            ':motorista_id' => $data['motorista_id'],
            ':secretaria_id' => null,
            ':secretaria' => $data['secretaria'],
            ':solicitante' => $data['solicitante'],
            ':origem' => $data['origem'],
            ':destino' => $data['destino'],
            ':finalidade' => $data['finalidade'],
            ':data_saida' => $data['data_saida'],
            ':data_retorno' => $data['data_retorno'],
            ':km_saida' => $data['km_saida'],
            ':km_chegada' => $data['km_chegada'],
            ':status' => $data['status'],
            ':observacoes' => $data['observacoes'],
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE viagens
             SET veiculo_id = :veiculo_id,
                 motorista_id = :motorista_id,
                 secretaria_id = :secretaria_id,
                 secretaria = :secretaria,
                 solicitante = :solicitante,
                 origem = :origem,
                 destino = :destino,
                 finalidade = :finalidade,
                 data_saida = :data_saida,
                 data_retorno = :data_retorno,
                 km_saida = :km_saida,
                 km_chegada = :km_chegada,
                 status = :status,
                 observacoes = :observacoes
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':veiculo_id' => $data['veiculo_id'],
            ':motorista_id' => $data['motorista_id'],
            ':secretaria_id' => null,
            ':secretaria' => $data['secretaria'],
            ':solicitante' => $data['solicitante'],
            ':origem' => $data['origem'],
            ':destino' => $data['destino'],
            ':finalidade' => $data['finalidade'],
            ':data_saida' => $data['data_saida'],
            ':data_retorno' => $data['data_retorno'],
            ':km_saida' => $data['km_saida'],
            ':km_chegada' => $data['km_chegada'],
            ':status' => $data['status'],
            ':observacoes' => $data['observacoes'],
        ]);
    }

    public function countEmCurso(): int
    {
        $stmt = $this->connection->query("SELECT COUNT(*) FROM viagens WHERE status = 'em_curso'");

        return (int) $stmt->fetchColumn();
    }

    private function resolveLegacyConnection(): PDO
    {
        global $pdo;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        throw new RuntimeException('Conexao PDO indisponivel para ViagemModel.');
    }
}
