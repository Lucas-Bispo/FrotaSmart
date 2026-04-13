<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Audit;

use FrotaSmart\Application\Audit\AuditEntry;
use FrotaSmart\Application\Contracts\AuditLoggerInterface;
use FrotaSmart\Infrastructure\Config\PdoConnectionFactory;
use PDO;
use Throwable;

final class PdoAuditLogger implements AuditLoggerInterface
{
    private ?PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection;
    }

    public function record(AuditEntry $entry): void
    {
        try {
            $data = $entry->toArray();
            $statement = $this->connection()->prepare(
                'INSERT INTO audit_logs (
                    event,
                    action,
                    target_type,
                    target_id,
                    actor,
                    actor_role,
                    ip,
                    occurred_at,
                    context_json
                ) VALUES (
                    :event,
                    :action,
                    :target_type,
                    :target_id,
                    :actor,
                    :actor_role,
                    :ip,
                    :occurred_at,
                    :context_json
                )'
            );

            $context = $data['context'];
            $statement->execute([
                ':event' => $data['event'],
                ':action' => $data['action'],
                ':target_type' => $data['target_type'],
                ':target_id' => $data['target_id'],
                ':actor' => $data['actor'],
                ':actor_role' => $this->resolveActorRole($context),
                ':ip' => $data['ip'],
                ':occurred_at' => $this->normalizeOccurredAt((string) $data['occurred_at']),
                ':context_json' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        } catch (Throwable $throwable) {
            error_log('Falha ao persistir auditoria no banco: ' . $throwable->getMessage());
        }
    }

    private function connection(): PDO
    {
        if (! $this->connection instanceof PDO) {
            $this->connection = PdoConnectionFactory::make();
        }

        return $this->connection;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveActorRole(array $context): ?string
    {
        $role = $context['actor_role'] ?? $_SESSION['role'] ?? null;

        return is_string($role) && $role !== '' ? $role : null;
    }

    private function normalizeOccurredAt(string $occurredAt): string
    {
        try {
            return (new \DateTimeImmutable($occurredAt))->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return date('Y-m-d H:i:s');
        }
    }
}
