<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Repositories;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\ValueObjects\Placa;

interface VeiculoRepositoryInterface
{
    public function save(Veiculo $veiculo): void;

    public function findActiveByPlaca(Placa $placa): ?Veiculo;

    public function findAnyByPlaca(Placa $placa): ?Veiculo;

    public function existsActiveByPlaca(Placa $placa): bool;

    public function existsAnyByPlaca(Placa $placa): bool;

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
