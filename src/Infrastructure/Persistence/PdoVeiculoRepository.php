<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Persistence;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\Repositories\VeiculoRepositoryInterface;
use FrotaSmart\Domain\ValueObjects\Placa;
use PDO;

final class PdoVeiculoRepository implements VeiculoRepositoryInterface
{
    private const STORAGE_STATUS_MAP = [
        'disponivel' => 'ativo',
        'em_manutencao' => 'manutencao',
        'em_viagem' => 'em_viagem',
        'reservado' => 'reservado',
        'baixado' => 'baixado',
    ];

    public function __construct(
        private readonly PDO $connection
    ) {
    }

    public function save(Veiculo $veiculo): void
    {
        if ($this->existsByPlaca($veiculo->placa())) {
            $statement = $this->connection->prepare(
                'UPDATE veiculos
                 SET modelo = :modelo,
                     status = :status,
                     renavam = :renavam,
                     chassi = :chassi,
                     ano_fabricacao = :ano_fabricacao,
                     tipo = :tipo,
                     combustivel = :combustivel,
                     secretaria_lotada = :secretaria_lotada,
                     quilometragem_inicial = :quilometragem_inicial,
                     data_aquisicao = :data_aquisicao,
                     documentos_observacoes = :documentos_observacoes,
                     deleted_at = NULL
                 WHERE placa = :placa'
            );
        } else {
            $statement = $this->connection->prepare(
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
                 ) VALUES (
                    :placa,
                    :modelo,
                    :status,
                    :renavam,
                    :chassi,
                    :ano_fabricacao,
                    :tipo,
                    :combustivel,
                    :secretaria_lotada,
                    :quilometragem_inicial,
                    :data_aquisicao,
                    :documentos_observacoes
                 )'
            );
        }

        $statement->execute([
            ':placa' => $veiculo->placaFormatada(),
            ':modelo' => $veiculo->modelo(),
            ':status' => $this->toStorageStatus($veiculo->status()),
            ':renavam' => $veiculo->renavam(),
            ':chassi' => $veiculo->chassi(),
            ':ano_fabricacao' => $veiculo->anoFabricacao(),
            ':tipo' => $veiculo->tipo(),
            ':combustivel' => $veiculo->combustivel(),
            ':secretaria_lotada' => $veiculo->secretariaLotada(),
            ':quilometragem_inicial' => $veiculo->quilometragemInicial(),
            ':data_aquisicao' => $veiculo->dataAquisicao(),
            ':documentos_observacoes' => $veiculo->documentosObservacoes(),
        ]);
    }

    public function findByPlaca(Placa $placa): ?Veiculo
    {
        $statement = $this->connection->prepare(
            'SELECT
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
             FROM veiculos
             WHERE placa = :placa
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute([':placa' => $placa->value()]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateVeiculo($row);
    }

    public function existsByPlaca(Placa $placa): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1 FROM veiculos WHERE placa = :placa AND deleted_at IS NULL LIMIT 1'
        );
        $statement->execute([':placa' => $placa->value()]);

        return $statement->fetchColumn() !== false;
    }

    public function findAll(): array
    {
        $statement = $this->connection->query(
            'SELECT
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
             FROM veiculos
             WHERE deleted_at IS NULL
             ORDER BY placa ASC'
        );

        $veiculos = [];

        foreach ($statement->fetchAll() as $row) {
            $veiculos[] = $this->hydrateVeiculo($row);
        }

        return $veiculos;
    }

    public function removeByPlaca(Placa $placa): void
    {
        $statement = $this->connection->prepare(
            'UPDATE veiculos SET deleted_at = CURRENT_TIMESTAMP WHERE placa = :placa'
        );

        $statement->execute([':placa' => $placa->value()]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateVeiculo(array $row): Veiculo
    {
        return new Veiculo(
            $row['placa'],
            $row['modelo'],
            $this->fromStorageStatus($row['status']),
            [
                'renavam' => $row['renavam'] ?? null,
                'chassi' => $row['chassi'] ?? null,
                'ano_fabricacao' => $row['ano_fabricacao'] ?? null,
                'tipo' => $row['tipo'] ?? null,
                'combustivel' => $row['combustivel'] ?? null,
                'secretaria_lotada' => $row['secretaria_lotada'] ?? null,
                'quilometragem_inicial' => $row['quilometragem_inicial'] ?? 0,
                'data_aquisicao' => $row['data_aquisicao'] ?? null,
                'documentos_observacoes' => $row['documentos_observacoes'] ?? null,
            ]
        );
    }

    private function toStorageStatus(string $domainStatus): string
    {
        return self::STORAGE_STATUS_MAP[$domainStatus] ?? 'ativo';
    }

    private function fromStorageStatus(string $storageStatus): string
    {
        return match ($storageStatus) {
            'ativo' => 'disponivel',
            'manutencao' => 'em_manutencao',
            default => $storageStatus,
        };
    }
}
