<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\ValueObjects\Placa;
use FrotaSmart\Infrastructure\Config\EnvLoader;
use FrotaSmart\Infrastructure\Config\PdoConnectionFactory;
use FrotaSmart\Infrastructure\Persistence\PdoVeiculoRepository;

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function ensureVeiculosTable(\PDO $connection): void
{
    $connection->exec(
        "CREATE TABLE IF NOT EXISTS veiculos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            placa VARCHAR(20) NOT NULL,
            modelo VARCHAR(100) NOT NULL,
            status ENUM('ativo', 'manutencao') NOT NULL DEFAULT 'ativo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_veiculos_placa (placa)
        )"
    );
}

EnvLoader::load();

try {
    $connection = PdoConnectionFactory::make();
    ensureVeiculosTable($connection);

    $repository = new PdoVeiculoRepository($connection);
    $placa = new Placa('TST1A23');

    $connection->prepare('DELETE FROM veiculos WHERE placa = :placa')->execute([':placa' => $placa->value()]);

    $repository->save(new Veiculo($placa, 'Veiculo de Teste', 'disponivel'));

    assertTrue($repository->existsByPlaca($placa), 'Repositorio deveria encontrar a placa salva.');

    $veiculo = $repository->findByPlaca($placa);
    assertTrue($veiculo instanceof Veiculo, 'Repositorio deveria hidratar um veiculo.');
    assertTrue($veiculo->status() === 'disponivel', 'Status legado deve ser traduzido para o dominio.');

    $repository->save(new Veiculo($placa, 'Veiculo Atualizado', 'em_manutencao'));
    $veiculoAtualizado = $repository->findByPlaca($placa);

    assertTrue($veiculoAtualizado instanceof Veiculo, 'Veiculo atualizado deveria continuar acessivel.');
    assertTrue($veiculoAtualizado->modelo() === 'Veiculo Atualizado', 'Modelo deveria ser atualizado.');
    assertTrue($veiculoAtualizado->status() === 'em_manutencao', 'Status deveria ser reidratado no padrao do dominio.');

    $repository->removeByPlaca($placa);
    assertTrue(! $repository->existsByPlaca($placa), 'Veiculo removido nao deveria continuar visivel.');

    echo "Repositorio PDO validado com sucesso." . PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Falha no teste do repositorio PDO: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
