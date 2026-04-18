<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class VeiculoModel
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT *
             FROM veiculos
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);

        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $veiculo !== false ? $veiculo : null;
    }

    public function addVeiculo(
        string $placa,
        string $modelo,
        string $status,
        ?string $renavam = null,
        ?string $chassi = null,
        ?int $anoFabricacao = null,
        ?string $tipo = null,
        ?string $combustivel = null,
        ?string $secretariaLotada = null,
        int $quilometragemInicial = 0,
        ?string $dataAquisicao = null,
        ?string $documentosObservacoes = null
    ): string {
        $stmt = $this->connection->prepare(
            'INSERT INTO veiculos (
                placa,
                modelo,
                status,
                renavam,
                chassi,
                ano_fabricacao,
                tipo,
                combustivel,
                secretaria_lotada,
                quilometragem_inicial,
                data_aquisicao,
                documentos_observacoes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $placa,
            $modelo,
            $status,
            $renavam,
            $chassi,
            $anoFabricacao,
            $tipo,
            $combustivel,
            $secretariaLotada,
            $quilometragemInicial,
            $dataAquisicao,
            $documentosObservacoes,
        ]);

        return $this->connection->lastInsertId();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllVeiculos(string $filtro = 'ativos'): array
    {
        $where = match ($filtro) {
            'arquivados' => 'deleted_at IS NOT NULL',
            'todos' => '1 = 1',
            default => 'deleted_at IS NULL',
        };

        $stmt = $this->connection->query(
            'SELECT *
             FROM veiculos
             WHERE ' . $where . '
             ORDER BY
                CASE
                    WHEN deleted_at IS NULL THEN 0
                    ELSE 1
                END,
                deleted_at DESC,
                CASE
                    WHEN status IN ("ativo", "disponivel", "em_viagem", "reservado") THEN 0
                    WHEN status IN ("manutencao", "em_manutencao") THEN 1
                    ELSE 2
                END,
                secretaria_lotada ASC,
                placa ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countArquivados(): int
    {
        $stmt = $this->connection->query('SELECT COUNT(*) FROM veiculos WHERE deleted_at IS NOT NULL');

        return (int) $stmt->fetchColumn();
    }

    public function updateVeiculo(
        int $id,
        string $placa,
        string $modelo,
        string $status,
        ?string $renavam = null,
        ?string $chassi = null,
        ?int $anoFabricacao = null,
        ?string $tipo = null,
        ?string $combustivel = null,
        ?string $secretariaLotada = null,
        int $quilometragemInicial = 0,
        ?string $dataAquisicao = null,
        ?string $documentosObservacoes = null
    ): int {
        $stmt = $this->connection->prepare(
            'UPDATE veiculos
             SET placa = ?,
                 modelo = ?,
                 status = ?,
                 renavam = ?,
                 chassi = ?,
                 ano_fabricacao = ?,
                 tipo = ?,
                 combustivel = ?,
                 secretaria_lotada = ?,
                 quilometragem_inicial = ?,
                 data_aquisicao = ?,
                 documentos_observacoes = ?
             WHERE id = ?
               AND deleted_at IS NULL'
        );

        $stmt->execute([
            $placa,
            $modelo,
            $status,
            $renavam,
            $chassi,
            $anoFabricacao,
            $tipo,
            $combustivel,
            $secretariaLotada,
            $quilometragemInicial,
            $dataAquisicao,
            $documentosObservacoes,
            $id,
        ]);

        return $stmt->rowCount();
    }

    public function deleteVeiculo(int $id): int
    {
        $stmt = $this->connection->prepare('UPDATE veiculos SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }

    public function restoreVeiculo(int $id): int
    {
        $stmt = $this->connection->prepare('UPDATE veiculos SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL');
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }

    private function resolveLegacyConnection(): PDO
    {
        return database_connection();
    }
}
