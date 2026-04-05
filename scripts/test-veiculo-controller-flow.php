<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;
require_once dirname(__DIR__) . '/backend/controllers/VeiculoController.php';

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

$service = new VeiculoService(new InMemoryVeiculoRepositoryForController());
$controller = new VeiculoController($service);

$resultadoAdd = $controller->processAdd([
    'placa' => 'ABC1D23',
    'modelo' => 'Onibus Escolar',
    'status' => 'disponivel',
]);

assertTrue($resultadoAdd['level'] === 'success', 'Cadastro deveria retornar sucesso.');
assertTrue($resultadoAdd['audit_event'] === 'veiculo.created', 'Cadastro deveria informar evento de auditoria.');

$resultadoUpdate = $controller->processUpdate([
    'placa_atual' => 'ABC1D23',
    'placa' => 'DEF1G23',
    'modelo' => 'Onibus Escolar Atualizado',
    'status' => 'em_manutencao',
]);

assertTrue($resultadoUpdate['level'] === 'success', 'Atualizacao deveria retornar sucesso.');
assertTrue(
    ($resultadoUpdate['audit_context']['placa'] ?? null) === 'DEF1G23',
    'Atualizacao deveria refletir a placa nova.'
);

$resultadoDelete = $controller->processDelete([
    'placa' => 'DEF1G23',
]);

assertTrue($resultadoDelete['level'] === 'success', 'Remocao deveria retornar sucesso.');
assertTrue($resultadoDelete['audit_event'] === 'veiculo.deleted', 'Remocao deveria informar evento de auditoria.');

echo "Fluxo do controller de veiculos validado com sucesso." . PHP_EOL;
