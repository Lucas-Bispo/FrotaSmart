<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Repositories;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\ValueObjects\Placa;

interface VeiculoRepositoryInterface
{
    public function save(Veiculo $veiculo): void;

    public function findByPlaca(Placa $placa): ?Veiculo;

    public function existsByPlaca(Placa $placa): bool;

    /**
     * @return list<Veiculo>
     */
    public function findAll(): array;

    public function removeByPlaca(Placa $placa): void;
}
