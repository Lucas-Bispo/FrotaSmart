<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Application\Exceptions\VeiculoAlreadyExistsException;
use FrotaSmart\Application\Exceptions\VeiculoNotFoundException;
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

function expectException(callable $callback, string $expectedException, string $message): void
{
    try {
        $callback();
    } catch (Throwable $throwable) {
        assertTrue(
            $throwable instanceof $expectedException,
            sprintf('%s Excecao recebida: %s', $message, $throwable::class)
        );

        return;
    }

    throw new RuntimeException($message . ' Nenhuma excecao foi lancada.');
}

final class InMemoryVeiculoRepository implements VeiculoRepositoryInterface
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
        ksort($this->items);

        return array_values($this->items);
    }

    public function removeByPlaca(Placa $placa): void
    {
        unset($this->items[$placa->value()]);
    }
}

$service = new VeiculoService(new InMemoryVeiculoRepository());

$veiculo = $service->cadastrar('ABC1D23', 'Onibus Escolar', 'ativo');
assertTrue($veiculo->status() === 'disponivel', 'Cadastro deve normalizar status legado.');

$buscado = $service->buscarPorPlaca('ABC1D23');
assertTrue($buscado instanceof Veiculo, 'Busca deve retornar o veiculo cadastrado.');
assertTrue($buscado->modelo() === 'Onibus Escolar', 'Busca deve preservar o modelo.');

$atualizado = $service->atualizar('ABC1D23', 'XYZ9K88', 'Van Adaptada', 'em_manutencao');
assertTrue($atualizado->placaFormatada() === 'XYZ9K88', 'Atualizacao deve permitir trocar a placa.');
assertTrue($atualizado->status() === 'em_manutencao', 'Atualizacao deve manter status oficial.');
assertTrue($service->buscarPorPlaca('ABC1D23') === null, 'Placa antiga nao deve permanecer ativa apos troca.');

$todos = $service->listarTodos();
assertTrue(count($todos) === 1, 'Listagem deve retornar apenas um registro ativo.');
assertTrue($todos[0]->placaFormatada() === 'XYZ9K88', 'Listagem deve refletir a placa atual.');

expectException(
    static fn () => $service->cadastrar('XYZ9K88', 'Outro Modelo', 'disponivel'),
    VeiculoAlreadyExistsException::class,
    'Nao deveria permitir cadastro duplicado.'
);

expectException(
    static fn () => $service->atualizar('NAO1A23', 'AAA1B23', 'Modelo', 'disponivel'),
    VeiculoNotFoundException::class,
    'Atualizacao deveria falhar para placa inexistente.'
);

$service->remover('XYZ9K88');
assertTrue($service->buscarPorPlaca('XYZ9K88') === null, 'Remocao deveria excluir o veiculo.');

expectException(
    static fn () => $service->remover('XYZ9K88'),
    VeiculoNotFoundException::class,
    'Remocao repetida deveria falhar.'
);

echo "Service de veiculos validado com sucesso." . PHP_EOL;
