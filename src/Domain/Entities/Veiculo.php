<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Entities;

final class Veiculo
{
    public function __construct(
        private readonly string $placa,
        private readonly string $modelo,
        private readonly string $status
    ) {
    }

    public function placa(): string
    {
        return $this->placa;
    }

    public function modelo(): string
    {
        return $this->modelo;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function descricao(): string
    {
        return sprintf('%s - %s (%s)', $this->placa, $this->modelo, $this->status);
    }
}
