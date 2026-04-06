<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Repositories;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\ValueObjects\Placa;

interface VeiculoRepositoryInterface
{
    public function save(Veiculo $veiculo): void;

    public function findByPlaca(Placa $placa, bool $includeArchived = false): ?Veiculo;

    public function existsByPlaca(Placa $placa, bool $includeArchived = false): bool;

    /**
     * @return list<Veiculo>
     */
    public function findAll(): array;

    /**
     * @return list<Veiculo>
     */
    public function findArchived(): array;

    public function removeByPlaca(Placa $placa): void;

    public function restoreByPlaca(Placa $placa): void;
}
