<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Contracts;

interface AuditContextProviderInterface
{
    public function actor(): ?string;

    public function ip(): ?string;
}
