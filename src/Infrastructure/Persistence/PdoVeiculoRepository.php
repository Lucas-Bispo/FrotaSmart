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
    ];

    public function __construct(
        private readonly PDO $connection
    ) {
    }

    public function save(Veiculo $veiculo): void
    {
        if ($this->existsByPlaca($veiculo->placa())) {
            $statement = $this->connection->prepare(
                'UPDATE veiculos SET modelo = :modelo, status = :status WHERE placa = :placa'
            );
        } else {
            $statement = $this->connection->prepare(
                'INSERT INTO veiculos (placa, modelo, status) VALUES (:placa, :modelo, :status)'
            );
        }

        $statement->execute([
            ':placa' => $veiculo->placaFormatada(),
            ':modelo' => $veiculo->modelo(),
            ':status' => $this->toStorageStatus($veiculo->status()),
        ]);
    }

    public function findByPlaca(Placa $placa): ?Veiculo
    {
        $statement = $this->connection->prepare(
            'SELECT placa, modelo, status FROM veiculos WHERE placa = :placa LIMIT 1'
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
            'SELECT 1 FROM veiculos WHERE placa = :placa LIMIT 1'
        );
        $statement->execute([':placa' => $placa->value()]);

        return $statement->fetchColumn() !== false;
    }

    public function findAll(): array
    {
        $statement = $this->connection->query(
            'SELECT placa, modelo, status FROM veiculos ORDER BY placa ASC'
        );

        $veiculos = [];

        foreach ($statement->fetchAll() as $row) {
            $veiculos[] = $this->hydrateVeiculo($row);
        }

        return $veiculos;
    }

    public function removeByPlaca(Placa $placa): void
    {
        if ($this->hasDeletedAtColumn()) {
            $statement = $this->connection->prepare(
                'UPDATE veiculos SET deleted_at = CURRENT_TIMESTAMP WHERE placa = :placa'
            );
        } else {
            $statement = $this->connection->prepare(
                'DELETE FROM veiculos WHERE placa = :placa'
            );
        }

        $statement->execute([':placa' => $placa->value()]);
    }

    /**
     * @param array{placa:string,modelo:string,status:string} $row
     */
    private function hydrateVeiculo(array $row): Veiculo
    {
        return new Veiculo(
            $row['placa'],
            $row['modelo'],
            $this->fromStorageStatus($row['status'])
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

    private function hasDeletedAtColumn(): bool
    {
        static $hasDeletedAtColumn;

        if ($hasDeletedAtColumn !== null) {
            return $hasDeletedAtColumn;
        }

        $statement = $this->connection->query("SHOW COLUMNS FROM veiculos LIKE 'deleted_at'");

        $hasDeletedAtColumn = $statement->fetch() !== false;

        return $hasDeletedAtColumn;
    }
}
