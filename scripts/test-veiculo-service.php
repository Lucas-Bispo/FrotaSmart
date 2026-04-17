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

    public function findActiveByPlaca(Placa $placa): ?Veiculo
    {
        $veiculo = $this->items[$placa->value()] ?? null;

        if ($veiculo === null) {
            return null;
        }

        if ($veiculo->estaArquivado()) {
            return null;
        }

        return $veiculo;
    }

    public function findAnyByPlaca(Placa $placa): ?Veiculo
    {
        return $this->items[$placa->value()] ?? null;
    }

    public function existsActiveByPlaca(Placa $placa): bool
    {
        return $this->findActiveByPlaca($placa) instanceof Veiculo;
    }

    public function existsAnyByPlaca(Placa $placa): bool
    {
        return $this->findAnyByPlaca($placa) instanceof Veiculo;
    }

    public function findAll(): array
    {
        $items = array_filter(
            $this->items,
            static fn (Veiculo $veiculo): bool => ! $veiculo->estaArquivado()
        );

        ksort($items);

        return array_values($items);
    }

    public function findArchived(): array
    {
        $items = array_filter(
            $this->items,
            static fn (Veiculo $veiculo): bool => $veiculo->estaArquivado()
        );

        ksort($items);

        return array_values($items);
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
            array_merge($veiculo->detalhesCadastro(), ['deleted_at' => '2026-04-05 20:00:00'])
        );
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

$service = new VeiculoService(new InMemoryVeiculoRepository());

$veiculo = $service->cadastrar('ABC1D23', 'Onibus Escolar', 'ativo', [
    'renavam' => '12345678901',
    'tipo' => 'Onibus',
    'combustivel' => 'diesel',
    'secretaria_lotada' => 'Educacao',
    'quilometragem_inicial' => 45200,
    'licenciamento_vencimento' => '2026-12-31',
]);
assertTrue($veiculo->status() === 'disponivel', 'Cadastro deve normalizar status legado.');
assertTrue($veiculo->secretariaLotada() === 'Educacao', 'Cadastro deve preservar secretaria lotada.');
assertTrue($veiculo->licenciamentoVencimento() === '2026-12-31', 'Cadastro deve preservar vencimento documental.');

$buscado = $service->buscarPorPlaca('ABC1D23');
assertTrue($buscado instanceof Veiculo, 'Busca deve retornar o veiculo cadastrado.');
assertTrue($buscado->modelo() === 'Onibus Escolar', 'Busca deve preservar o modelo.');
assertTrue($buscado->renavam() === '12345678901', 'Busca deve preservar RENAVAM.');

$atualizado = $service->atualizar('ABC1D23', 'XYZ9K88', 'Van Adaptada', 'em_manutencao', [
    'tipo' => 'Van',
    'combustivel' => 'flex',
    'secretaria_lotada' => 'Saude',
    'quilometragem_inicial' => 1200,
    'data_aquisicao' => '2026-02-01',
    'seguro_vencimento' => '2026-09-15',
]);
assertTrue($atualizado->placaFormatada() === 'XYZ9K88', 'Atualizacao deve permitir trocar a placa.');
assertTrue($atualizado->status() === 'em_manutencao', 'Atualizacao deve manter status oficial.');
assertTrue($atualizado->combustivel() === 'flex', 'Atualizacao deve persistir combustivel.');
assertTrue($atualizado->seguroVencimento() === '2026-09-15', 'Atualizacao deve preservar vencimento do seguro.');
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

$service->arquivar('XYZ9K88');
assertTrue($service->buscarPorPlaca('XYZ9K88') === null, 'Remocao deveria excluir o veiculo.');
assertTrue($service->buscarPorPlacaIncluindoArquivados('XYZ9K88')?->estaArquivado() === true, 'Veiculo arquivado deve continuar acessivel no historico.');
$arquivados = $service->listarArquivados();
assertTrue(count($arquivados) >= 1, 'Listagem de arquivados deve retornar registros arquivados.');
assertTrue(
    array_filter($arquivados, static fn (Veiculo $item): bool => $item->placaFormatada() === 'XYZ9K88') !== [],
    'Listagem de arquivados deve incluir a placa arquivada.'
);

$service->restaurar('XYZ9K88');
assertTrue($service->buscarPorPlaca('XYZ9K88') instanceof Veiculo, 'Restauracao deve devolver o veiculo para a listagem ativa.');

expectException(
    static fn () => $service->restaurar('AAA1B23'),
    VeiculoNotFoundException::class,
    'Restauracao deveria falhar para placa inexistente.'
);

echo "Service de veiculos validado com sucesso." . PHP_EOL;
