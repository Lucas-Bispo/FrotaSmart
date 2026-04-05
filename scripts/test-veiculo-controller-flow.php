<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;
require_once dirname(__DIR__) . '/backend/controllers/VeiculoController.php';

use FrotaSmart\Application\Audit\AuditEntry;
use FrotaSmart\Application\Contracts\AuditContextProviderInterface;
use FrotaSmart\Application\Contracts\AuditLoggerInterface;
use FrotaSmart\Application\Services\AuditTrailService;
use FrotaSmart\Application\Services\VeiculoService;
use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\Repositories\VeiculoRepositoryInterface;
use FrotaSmart\Domain\ValueObjects\Placa;

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

final class InMemoryVeiculoRepositoryForController implements VeiculoRepositoryInterface
{
    /**
     * @var array<string, Veiculo>
     */
    private array $items = [];

    public function save(Veiculo $veiculo): void
    {
        $this->items[$veiculo->placaFormatada()] = $veiculo;
    }

    public function findByPlaca(Placa $placa): ?Veiculo
    {
        return $this->items[$placa->value()] ?? null;
    }

    public function existsByPlaca(Placa $placa): bool
    {
        return isset($this->items[$placa->value()]);
    }

    public function findAll(): array
    {
        return array_values($this->items);
    }

    public function removeByPlaca(Placa $placa): void
    {
        unset($this->items[$placa->value()]);
    }
}

final class InMemoryAuditLoggerForController implements AuditLoggerInterface
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

final class FixedAuditContextProviderForController implements AuditContextProviderInterface
{
    public function actor(): ?string
    {
        return 'gerente_frota';
    }

    public function ip(): ?string
    {
        return '192.168.0.10';
    }
}

$service = new VeiculoService(new InMemoryVeiculoRepositoryForController());
$auditLogger = new InMemoryAuditLoggerForController();
$controller = new VeiculoController(
    $service,
    new AuditTrailService($auditLogger, new FixedAuditContextProviderForController())
);

$resultadoAdd = $controller->processAdd([
    'placa' => 'ABC1D23',
    'modelo' => 'Onibus Escolar',
    'status' => 'disponivel',
    'tipo' => 'Onibus',
    'combustivel' => 'diesel',
    'secretaria_lotada' => 'Educacao',
    'quilometragem_inicial' => '45200',
]);

assertTrue($resultadoAdd['level'] === 'success', 'Cadastro deveria retornar sucesso.');

$resultadoUpdate = $controller->processUpdate([
    'placa_atual' => 'ABC1D23',
    'placa' => 'DEF1G23',
    'modelo' => 'Onibus Escolar Atualizado',
    'status' => 'em_manutencao',
    'tipo' => 'Micro-onibus',
    'combustivel' => 'diesel_s10',
    'secretaria_lotada' => 'Saude',
    'ano_fabricacao' => '2025',
]);

assertTrue($resultadoUpdate['level'] === 'success', 'Atualizacao deveria retornar sucesso.');

$resultadoDelete = $controller->processDelete([
    'placa' => 'DEF1G23',
]);

assertTrue($resultadoDelete['level'] === 'success', 'Remocao deveria retornar sucesso.');
assertTrue(count($auditLogger->entries) === 3, 'Controller deveria registrar auditoria para tres operacoes.');

$ultimoEvento = $auditLogger->entries[2]->toArray();
assertTrue($ultimoEvento['event'] === 'veiculo.deleted', 'Remocao deveria gerar evento de exclusao.');
assertTrue($ultimoEvento['actor'] === 'gerente_frota', 'Auditoria deveria preservar o ator do contexto.');

echo "Fluxo do controller de veiculos validado com sucesso." . PHP_EOL;
