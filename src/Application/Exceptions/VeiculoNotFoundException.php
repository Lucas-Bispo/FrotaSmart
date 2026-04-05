<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Exceptions;

final class VeiculoNotFoundException extends ApplicationException
{
    public static function forPlaca(string $placa): self
    {
        return new self(sprintf('Veiculo nao encontrado para a placa "%s".', $placa));
    }
}
