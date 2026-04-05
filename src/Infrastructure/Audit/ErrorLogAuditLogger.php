<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Audit;

use FrotaSmart\Application\Audit\AuditEntry;
use FrotaSmart\Application\Contracts\AuditLoggerInterface;

final class ErrorLogAuditLogger implements AuditLoggerInterface
{
    public function record(AuditEntry $entry): void
    {
        error_log('[AUDIT] ' . json_encode($entry->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
