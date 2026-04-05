<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class ParceiroOperacionalModel
{
    public function getAll(?string $tipo = null, ?string $status = null): array
    {
        global $pdo;

        $conditions = [];
        $params = [];

        if ($tipo !== null && $tipo !== '') {
            $conditions[] = 'tipo = :tipo';
            $params[':tipo'] = $tipo;
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 'status = :status';
            $params[':status'] = $status;
        }

        $sql = 'SELECT *
                FROM parceiros_operacionais';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY nome_fantasia ASC, id ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveByTipos(array $tipos): array
    {
        global $pdo;

        if ($tipos === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($tipos), '?'));
        $params = array_merge($tipos, ['ativo']);

        $stmt = $pdo->prepare(
            "SELECT *
             FROM parceiros_operacionais
             WHERE tipo IN ($placeholders)
               AND status = ?
             ORDER BY nome_fantasia ASC, id ASC"
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        global $pdo;

        $stmt = $pdo->prepare('SELECT * FROM parceiros_operacionais WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }

    public function create(array $data): int
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'INSERT INTO parceiros_operacionais (
                nome_fantasia,
                razao_social,
                cnpj,
                tipo,
                telefone,
                endereco,
                contato_responsavel,
                status,
                observacoes
             ) VALUES (
                :nome_fantasia,
                :razao_social,
                :cnpj,
                :tipo,
                :telefone,
                :endereco,
                :contato_responsavel,
                :status,
                :observacoes
             )'
        );

        $stmt->execute([
            ':nome_fantasia' => $data['nome_fantasia'],
            ':razao_social' => $data['razao_social'],
            ':cnpj' => $data['cnpj'],
            ':tipo' => $data['tipo'],
            ':telefone' => $data['telefone'],
            ':endereco' => $data['endereco'],
            ':contato_responsavel' => $data['contato_responsavel'],
            ':status' => $data['status'],
            ':observacoes' => $data['observacoes'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'UPDATE parceiros_operacionais
             SET nome_fantasia = :nome_fantasia,
                 razao_social = :razao_social,
                 cnpj = :cnpj,
                 tipo = :tipo,
                 telefone = :telefone,
                 endereco = :endereco,
                 contato_responsavel = :contato_responsavel,
                 status = :status,
                 observacoes = :observacoes
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':nome_fantasia' => $data['nome_fantasia'],
            ':razao_social' => $data['razao_social'],
            ':cnpj' => $data['cnpj'],
            ':tipo' => $data['tipo'],
            ':telefone' => $data['telefone'],
            ':endereco' => $data['endereco'],
            ':contato_responsavel' => $data['contato_responsavel'],
            ':status' => $data['status'],
            ':observacoes' => $data['observacoes'],
        ]);
    }
}
