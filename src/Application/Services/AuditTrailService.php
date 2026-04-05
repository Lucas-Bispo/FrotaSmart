<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

use FrotaSmart\Application\Audit\AuditEntry;
use FrotaSmart\Application\Contracts\AuditContextProviderInterface;
use FrotaSmart\Application\Contracts\AuditLoggerInterface;

final class AuditTrailService
{
    public function __construct(
        private readonly AuditLoggerInterface $logger,
        private readonly AuditContextProviderInterface $contextProvider
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function recordMutation(
        string $event,
        string $action,
        string $targetType,
        string $targetId,
        array $context = []
    ): AuditEntry {
        $entry = AuditEntry::mutation(
            $event,
            $action,
            $targetType,
            $targetId,
            $this->contextProvider->actor(),
            $this->contextProvider->ip(),
            $context
        );

        $this->logger->record($entry);

        return $entry;
    }
}
