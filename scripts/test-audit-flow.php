<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Application\Audit\AuditEntry;
use FrotaSmart\Application\Contracts\AuditContextProviderInterface;
use FrotaSmart\Application\Contracts\AuditLoggerInterface;
use FrotaSmart\Application\Services\AuditTrailService;

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

final class InMemoryAuditLogger implements AuditLoggerInterface
{
    /**
     * @var list<AuditEntry>
     */
    public array $entries = [];

    public function record(AuditEntry $entry): void
    {
        $this->entries[] = $entry;
    }
}

final class FixedAuditContextProvider implements AuditContextProviderInterface
{
    public function actor(): ?string
    {
        return 'admin_frota';
    }

    public function ip(): ?string
    {
        return '127.0.0.1';
    }
}

$logger = new InMemoryAuditLogger();
$auditTrail = new AuditTrailService($logger, new FixedAuditContextProvider());

$auditTrail->recordMutation('veiculo.created', 'create', 'veiculo', 'ABC1D23', [
    'placa' => 'ABC1D23',
    'status' => 'disponivel',
]);

$auditTrail->recordMutation('veiculo.updated', 'update', 'veiculo', 'ABC1D23', [
    'placa' => 'ABC1D23',
    'status' => 'em_manutencao',
]);

$auditTrail->recordMutation('veiculo.deleted', 'delete', 'veiculo', 'ABC1D23', [
    'placa' => 'ABC1D23',
    'status' => 'em_manutencao',
]);

assertTrue(count($logger->entries) === 3, 'Auditoria deveria registrar tres eventos.');

$payload = $logger->entries[0]->toArray();
assertTrue($payload['event'] === 'veiculo.created', 'Evento deveria preservar o nome informado.');
assertTrue($payload['action'] === 'create', 'Evento deveria carregar a acao.');
assertTrue($payload['target_type'] === 'veiculo', 'Evento deveria carregar o tipo de alvo.');
assertTrue($payload['target_id'] === 'ABC1D23', 'Evento deveria carregar o alvo principal.');
assertTrue($payload['actor'] === 'admin_frota', 'Evento deveria carregar o ator.');
assertTrue($payload['ip'] === '127.0.0.1', 'Evento deveria carregar o IP.');
assertTrue(isset($payload['occurred_at']) && is_string($payload['occurred_at']), 'Evento deveria carregar data.');

echo "Fluxo de auditoria validado com sucesso." . PHP_EOL;
