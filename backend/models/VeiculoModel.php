<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class VeiculoModel
{
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
        global $pdo;

        $stmt = $pdo->prepare(
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

        return $pdo->lastInsertId();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllVeiculos(): array
    {
        global $pdo;

        $stmt = $pdo->query(
            'SELECT *
             FROM veiculos
             WHERE deleted_at IS NULL
             ORDER BY
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
        global $pdo;

        $stmt = $pdo->prepare(
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
        global $pdo;

        $stmt = $pdo->prepare('UPDATE veiculos SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);

        return $stmt->rowCount();
    }
}
