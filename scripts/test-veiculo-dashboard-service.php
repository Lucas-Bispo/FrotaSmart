<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Application\Services\VeiculoDashboardService;
use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\Repositories\VeiculoRepositoryInterface;
use FrotaSmart\Domain\ValueObjects\Placa;

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

final class InMemoryVeiculoDashboardRepository implements VeiculoRepositoryInterface
{
    /**
     * @param list<Veiculo> $ativos
     * @param list<Veiculo> $arquivados
     */
    public function __construct(
        private readonly array $ativos,
        private readonly array $arquivados
    ) {
    }

    public function save(Veiculo $veiculo): void
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }

    public function findActiveByPlaca(Placa $placa): ?Veiculo
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }

    public function findAnyByPlaca(Placa $placa): ?Veiculo
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }

    public function existsActiveByPlaca(Placa $placa): bool
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }

    public function existsAnyByPlaca(Placa $placa): bool
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }

    public function findAll(): array
    {
        return $this->ativos;
    }

    public function findArchived(): array
    {
        return $this->arquivados;
    }

    public function removeByPlaca(Placa $placa): void
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }

    public function restoreByPlaca(Placa $placa): void
    {
        throw new BadMethodCallException('Nao utilizado neste teste.');
    }
}

$repository = new InMemoryVeiculoDashboardRepository(
    [
        new Veiculo('BBB1C23', 'Ambulancia B', 'em_manutencao', [
            'secretaria_lotada' => 'Saude',
            'tipo' => 'Ambulancia',
            'combustivel' => 'diesel',
            'quilometragem_inicial' => 82000,
            'licenciamento_vencimento' => '2026-12-31',
        ]),
        new Veiculo('AAA1B23', 'Van A', 'disponivel', [
            'secretaria_lotada' => 'Educacao',
            'tipo' => 'Van',
            'combustivel' => 'flex',
            'quilometragem_inicial' => 12000,
            'crlv_vencimento' => '2026-10-15',
        ]),
    ],
    [
        new Veiculo('ZZZ9Z99', 'Onibus Arquivado', 'baixado', [
            'secretaria_lotada' => 'Administracao',
            'deleted_at' => '2026-04-12 08:00:00',
        ]),
        new Veiculo('YYY8Y88', 'Carro Arquivado Antigo', 'baixado', [
            'secretaria_lotada' => 'Obras',
            'deleted_at' => '2026-04-10 09:30:00',
        ]),
    ]
);

$service = new VeiculoDashboardService($repository);

$ativos = $service->listarPorFiltro('ativos');
assertTrue(count($ativos) === 2, 'Filtro ativos deve retornar apenas veiculos ativos.');
assertTrue($ativos[0]['placa'] === 'AAA1B23', 'Veiculo disponivel deve vir antes do em manutencao.');
assertTrue($ativos[1]['placa'] === 'BBB1C23', 'Veiculo em manutencao deve aparecer depois dos disponiveis.');
assertTrue($ativos[0]['crlv_vencimento'] === '2026-10-15', 'Dashboard deve expor vencimentos documentais no pacote do veiculo.');

$todos = $service->listarPorFiltro('todos');
assertTrue(count($todos) === 4, 'Filtro todos deve combinar ativos e arquivados.');
assertTrue($todos[2]['placa'] === 'ZZZ9Z99', 'Arquivados mais recentes devem aparecer primeiro entre os arquivados.');
assertTrue($todos[3]['placa'] === 'YYY8Y88', 'Arquivados mais antigos devem aparecer depois.');

$arquivados = $service->listarPorFiltro('arquivados');
assertTrue(count($arquivados) === 2, 'Filtro arquivados deve retornar apenas historico.');
assertTrue($service->contarArquivados() === 2, 'Contagem de arquivados deve refletir o historico.');
assertTrue($arquivados[0]['deleted_at'] === '2026-04-12 08:00:00', 'Leitura deve expor data de arquivamento para o dashboard.');

echo "Dashboard de veiculos validado com sucesso." . PHP_EOL;
