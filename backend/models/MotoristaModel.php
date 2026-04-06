<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class MotoristaModel
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getAllMotoristas(): array
    {
        global $pdo;

        $stmt = $pdo->query(
            'SELECT id, nome, cpf, telefone, secretaria, cnh_numero, cnh_categoria, cnh_vencimento, status, user_id, created_at, updated_at
             FROM motoristas
             ORDER BY nome ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'SELECT id, nome, cpf, telefone, secretaria, cnh_numero, cnh_categoria, cnh_vencimento, status, user_id, created_at, updated_at
             FROM motoristas
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $motorista = $stmt->fetch(PDO::FETCH_ASSOC);

        return $motorista !== false ? $motorista : null;
    }

    public function countCnhsVencendo(int $days = 30): int
    {
        global $pdo;

        $today = new DateTimeImmutable('today');
        $limit = $today->modify('+' . max(1, $days) . ' days');

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM motoristas
             WHERE status = :status
               AND cnh_vencimento BETWEEN :inicio AND :fim'
        );
        $stmt->execute([
            ':status' => 'ativo',
            ':inicio' => $today->format('Y-m-d'),
            ':fim' => $limit->format('Y-m-d'),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): void
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'INSERT INTO motoristas (nome, cpf, telefone, secretaria, cnh_numero, cnh_categoria, cnh_vencimento, status)
             VALUES (:nome, :cpf, :telefone, :secretaria, :cnh_numero, :cnh_categoria, :cnh_vencimento, :status)'
        );

        $stmt->execute([
            ':nome' => $data['nome'],
            ':cpf' => $data['cpf'],
            ':telefone' => $data['telefone'],
            ':secretaria' => $data['secretaria'],
            ':cnh_numero' => $data['cnh_numero'],
            ':cnh_categoria' => $data['cnh_categoria'],
            ':cnh_vencimento' => $data['cnh_vencimento'],
            ':status' => $data['status'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'UPDATE motoristas
             SET nome = :nome,
                 cpf = :cpf,
                 telefone = :telefone,
                 secretaria = :secretaria,
                 cnh_numero = :cnh_numero,
                 cnh_categoria = :cnh_categoria,
                 cnh_vencimento = :cnh_vencimento,
                 status = :status
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':nome' => $data['nome'],
            ':cpf' => $data['cpf'],
            ':telefone' => $data['telefone'],
            ':secretaria' => $data['secretaria'],
            ':cnh_numero' => $data['cnh_numero'],
            ':cnh_categoria' => $data['cnh_categoria'],
            ':cnh_vencimento' => $data['cnh_vencimento'],
            ':status' => $data['status'],
        ]);
    }
}
