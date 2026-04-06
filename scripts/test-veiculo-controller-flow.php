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

    public function findByPlaca(Placa $placa, bool $includeArchived = false): ?Veiculo
    {
        $veiculo = $this->items[$placa->value()] ?? null;

        if ($veiculo === null) {
            return null;
        }

        if (! $includeArchived && $veiculo->estaArquivado()) {
            return null;
        }

        return $veiculo;
    }

    public function existsByPlaca(Placa $placa, bool $includeArchived = false): bool
    {
        return $this->findByPlaca($placa, $includeArchived) instanceof Veiculo;
    }

    public function findAll(): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (Veiculo $veiculo): bool => ! $veiculo->estaArquivado()
        ));
    }

    public function removeByPlaca(Placa $placa): void
    {
        $veiculo = $this->items[$placa->value()] ?? null;

        if ($veiculo === null) {
            return;
        }

        $this->items[$placa->value()] = new Veiculo(
            $veiculo->placa(),
            $veiculo->modelo(),
            $veiculo->status(),
            array_merge($veiculo->detalhesCadastro(), ['deleted_at' => '2026-04-05 20:05:00'])
        );
    }

    public function findArchived(): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (Veiculo $veiculo): bool => $veiculo->estaArquivado()
        ));
    }

    public function restoreByPlaca(Placa $placa): void
    {
        $veiculo = $this->items[$placa->value()] ?? null;

        if ($veiculo === null) {
            return;
        }

        $this->items[$placa->value()] = new Veiculo(
            $veiculo->placa(),
            $veiculo->modelo(),
            $veiculo->status(),
            array_merge($veiculo->detalhesCadastro(), ['deleted_at' => null])
        );
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

$resultadoDelete = $controller->processArchive([
    'placa' => 'DEF1G23',
]);

assertTrue($resultadoDelete['level'] === 'success', 'Arquivamento deveria retornar sucesso.');

$resultadoRestore = $controller->processRestore([
    'placa' => 'DEF1G23',
]);

assertTrue($resultadoRestore['level'] === 'success', 'Restauracao deveria retornar sucesso.');
assertTrue(count($auditLogger->entries) === 4, 'Controller deveria registrar auditoria para quatro operacoes.');

$ultimoEvento = $auditLogger->entries[2]->toArray();
assertTrue($ultimoEvento['event'] === 'veiculo.archived', 'Arquivamento deveria gerar evento dedicado.');
assertTrue($ultimoEvento['actor'] === 'gerente_frota', 'Auditoria deveria preservar o ator do contexto.');

$eventoRestauracao = $auditLogger->entries[3]->toArray();
assertTrue($eventoRestauracao['event'] === 'veiculo.restored', 'Restauracao deveria gerar evento dedicado.');

echo "Fluxo do controller de veiculos validado com sucesso." . PHP_EOL;
