<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Exceptions;

final class InvalidVeiculoStatusException extends DomainException
{
    public static function unsupportedStatus(string $status): self
    {
        return new self(sprintf('Status de veiculo nao suportado: "%s".', $status));
    }

    public static function transitionNotAllowed(string $currentStatus, string $targetStatus): self
    {
        return new self(
            sprintf(
                'Transicao de status nao permitida: "%s" -> "%s".',
                $currentStatus,
                $targetStatus
            )
        );
    }
}
