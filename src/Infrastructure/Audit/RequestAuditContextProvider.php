<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Audit;

use FrotaSmart\Application\Contracts\AuditContextProviderInterface;

final class RequestAuditContextProvider implements AuditContextProviderInterface
{
    public function actor(): ?string
    {
        return isset($_SESSION['user']) && is_string($_SESSION['user'])
            ? $_SESSION['user']
            : null;
    }

    public function ip(): ?string
    {
        return isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : 'cli';
    }
}
