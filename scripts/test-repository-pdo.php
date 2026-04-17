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
            renavam VARCHAR(20) DEFAULT NULL,
            chassi VARCHAR(30) DEFAULT NULL,
            ano_fabricacao SMALLINT DEFAULT NULL,
            tipo VARCHAR(50) DEFAULT NULL,
            combustivel VARCHAR(30) DEFAULT NULL,
            secretaria_lotada VARCHAR(100) DEFAULT NULL,
            quilometragem_inicial INT NOT NULL DEFAULT 0,
            data_aquisicao DATE DEFAULT NULL,
            licenciamento_vencimento DATE DEFAULT NULL,
            seguro_vencimento DATE DEFAULT NULL,
            crlv_vencimento DATE DEFAULT NULL,
            contrato_vencimento DATE DEFAULT NULL,
            documentos_observacoes TEXT DEFAULT NULL,
            status ENUM('ativo', 'manutencao', 'em_viagem', 'reservado', 'baixado') NOT NULL DEFAULT 'ativo',
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_veiculos_placa (placa),
            UNIQUE KEY uk_veiculos_renavam (renavam),
            UNIQUE KEY uk_veiculos_chassi (chassi)
        )"
    );

    $columns = [
        "ALTER TABLE veiculos ADD COLUMN renavam VARCHAR(20) NULL AFTER modelo",
        "ALTER TABLE veiculos ADD COLUMN chassi VARCHAR(30) NULL AFTER renavam",
        "ALTER TABLE veiculos ADD COLUMN ano_fabricacao SMALLINT NULL AFTER chassi",
        "ALTER TABLE veiculos ADD COLUMN tipo VARCHAR(50) NULL AFTER ano_fabricacao",
        "ALTER TABLE veiculos ADD COLUMN combustivel VARCHAR(30) NULL AFTER tipo",
        "ALTER TABLE veiculos ADD COLUMN secretaria_lotada VARCHAR(100) NULL AFTER combustivel",
        "ALTER TABLE veiculos ADD COLUMN quilometragem_inicial INT NOT NULL DEFAULT 0 AFTER secretaria_lotada",
        "ALTER TABLE veiculos ADD COLUMN data_aquisicao DATE NULL AFTER quilometragem_inicial",
        "ALTER TABLE veiculos ADD COLUMN licenciamento_vencimento DATE NULL AFTER data_aquisicao",
        "ALTER TABLE veiculos ADD COLUMN seguro_vencimento DATE NULL AFTER licenciamento_vencimento",
        "ALTER TABLE veiculos ADD COLUMN crlv_vencimento DATE NULL AFTER seguro_vencimento",
        "ALTER TABLE veiculos ADD COLUMN contrato_vencimento DATE NULL AFTER crlv_vencimento",
        "ALTER TABLE veiculos ADD COLUMN documentos_observacoes TEXT NULL AFTER contrato_vencimento",
        "ALTER TABLE veiculos ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER status",
    ];

    foreach ($columns as $statement) {
        try {
            $connection->exec($statement);
        } catch (Throwable) {
        }
    }
}

EnvLoader::load();

try {
    $connection = PdoConnectionFactory::make();
    ensureVeiculosTable($connection);

    $repository = new PdoVeiculoRepository($connection);
    $placa = new Placa('TST1A23');

    $connection->prepare('DELETE FROM veiculos WHERE placa = :placa')->execute([':placa' => $placa->value()]);

    $repository->save(new Veiculo($placa, 'Veiculo de Teste', 'disponivel', [
        'renavam' => '12345678901',
        'chassi' => '9BWZZZ377VT004251',
        'tipo' => 'Van',
        'combustivel' => 'flex',
        'secretaria_lotada' => 'Saude',
        'quilometragem_inicial' => 8000,
        'data_aquisicao' => '2026-01-15',
        'licenciamento_vencimento' => '2026-12-31',
        'crlv_vencimento' => '2026-11-30',
    ]));

    assertTrue($repository->existsActiveByPlaca($placa), 'Repositorio deveria encontrar a placa salva.');

    $veiculo = $repository->findActiveByPlaca($placa);
    assertTrue($veiculo instanceof Veiculo, 'Repositorio deveria hidratar um veiculo.');
    assertTrue($veiculo->status() === 'disponivel', 'Status legado deve ser traduzido para o dominio.');
    assertTrue($veiculo->secretariaLotada() === 'Saude', 'Repositorio deve hidratar secretaria lotada.');
    assertTrue($veiculo->licenciamentoVencimento() === '2026-12-31', 'Repositorio deve hidratar licenciamento.');

    $repository->save(new Veiculo($placa, 'Veiculo Atualizado', 'em_manutencao', [
        'tipo' => 'Ambulancia',
        'combustivel' => 'diesel_s10',
        'quilometragem_inicial' => 8200,
    ]));
    $veiculoAtualizado = $repository->findActiveByPlaca($placa);

    assertTrue($veiculoAtualizado instanceof Veiculo, 'Veiculo atualizado deveria continuar acessivel.');
    assertTrue($veiculoAtualizado->modelo() === 'Veiculo Atualizado', 'Modelo deveria ser atualizado.');
    assertTrue($veiculoAtualizado->status() === 'em_manutencao', 'Status deveria ser reidratado no padrao do dominio.');
    assertTrue($veiculoAtualizado->tipo() === 'Ambulancia', 'Tipo deveria ser atualizado.');

    $repository->removeByPlaca($placa);
    assertTrue(! $repository->existsActiveByPlaca($placa), 'Veiculo removido nao deveria continuar visivel.');
    assertTrue($repository->existsAnyByPlaca($placa), 'Veiculo arquivado deve continuar existindo no historico.');
    assertTrue(count($repository->findArchived()) >= 1, 'Repositorio deveria listar veiculos arquivados.');

    $veiculoArquivado = $repository->findAnyByPlaca($placa);
    assertTrue($veiculoArquivado instanceof Veiculo && $veiculoArquivado->estaArquivado(), 'Busca expandida deve hidratar veiculo arquivado.');

    $repository->restoreByPlaca($placa);
    assertTrue($repository->existsActiveByPlaca($placa), 'Veiculo restaurado deve voltar a ficar visivel.');

    echo "Repositorio PDO validado com sucesso." . PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Falha no teste do repositorio PDO: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
