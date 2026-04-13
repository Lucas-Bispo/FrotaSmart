<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Audit;

use FrotaSmart\Application\Audit\AuditEntry;
use FrotaSmart\Application\Contracts\AuditLoggerInterface;
use Throwable;

final class CompositeAuditLogger implements AuditLoggerInterface
{
    /**
     * @param list<AuditLoggerInterface> $loggers
     */
    public function __construct(
        private readonly array $loggers
    ) {
    }

    public function record(AuditEntry $entry): void
    {
        foreach ($this->loggers as $logger) {
            try {
                $logger->record($entry);
            } catch (Throwable $throwable) {
                error_log(sprintf(
                    'Falha ao registrar auditoria com %s: %s',
                    $logger::class,
                    $throwable->getMessage()
                ));
            }
        }
    }
}
