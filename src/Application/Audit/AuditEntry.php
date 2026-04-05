<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Audit;

use DateTimeImmutable;
use DateTimeInterface;

final class AuditEntry
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly string $event,
        private readonly string $action,
        private readonly string $targetType,
        private readonly string $targetId,
        private readonly ?string $actor,
        private readonly ?string $ip,
        private readonly DateTimeImmutable $occurredAt,
        private readonly array $context = []
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function mutation(
        string $event,
        string $action,
        string $targetType,
        string $targetId,
        ?string $actor,
        ?string $ip,
        array $context = []
    ): self {
        return new self(
            $event,
            $action,
            $targetType,
            $targetId,
            $actor,
            $ip,
            new DateTimeImmutable('now'),
            $context
        );
    }

    /**
     * @return array{
     *   event:string,
     *   action:string,
     *   target_type:string,
     *   target_id:string,
     *   actor:?string,
     *   ip:?string,
     *   occurred_at:string,
     *   context:array<string,mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'event' => $this->event,
            'action' => $this->action,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'actor' => $this->actor,
            'ip' => $this->ip,
            'occurred_at' => $this->occurredAt->format(DateTimeInterface::ATOM),
            'context' => $this->context,
        ];
    }
}
