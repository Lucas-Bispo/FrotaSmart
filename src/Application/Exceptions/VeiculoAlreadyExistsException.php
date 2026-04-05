<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Exceptions;

final class VeiculoAlreadyExistsException extends ApplicationException
{
    public static function forPlaca(string $placa): self
    {
        return new self(sprintf('Ja existe veiculo cadastrado com a placa "%s".', $placa));
    }
}
