<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Contracts;

use FrotaSmart\Application\Audit\AuditEntry;

interface AuditLoggerInterface
{
    public function record(AuditEntry $entry): void;
}
