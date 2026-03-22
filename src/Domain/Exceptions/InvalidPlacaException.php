<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Exceptions;

final class InvalidPlacaException extends DomainException
{
    public static function forValue(string $placa): self
    {
        return new self(sprintf('Placa invalida informada: "%s".', $placa));
    }
}
