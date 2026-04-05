<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Domain\Entities\Veiculo;
use FrotaSmart\Domain\Exceptions\DomainException;
use FrotaSmart\Domain\Exceptions\InvalidPlacaException;

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

$veiculoAntigo = new Veiculo('ABC1234', 'Fiat Uno', 'ativo');
assertTrue($veiculoAntigo->placaFormatada() === 'ABC1234', 'A placa antiga deve ser mantida normalizada.');
assertTrue($veiculoAntigo->status() === 'disponivel', 'Status legado `ativo` deve virar `disponivel`.');

$veiculoMercosul = new Veiculo('abc1d23', 'Onibus Escolar', 'reservado');
assertTrue($veiculoMercosul->placaFormatada() === 'ABC1D23', 'Placa Mercosul deve ser normalizada.');
$veiculoMercosul->iniciarViagem();
assertTrue($veiculoMercosul->status() === 'em_viagem', 'Veiculo reservado deve poder iniciar viagem.');
$veiculoMercosul->liberarParaUso();
assertTrue($veiculoMercosul->estaDisponivel(), 'Veiculo em viagem deve poder voltar para disponivel.');

$veiculoCompleto = new Veiculo('QWE1R23', 'Pickup 4x4', 'disponivel', [
    'renavam' => '12345678901',
    'chassi' => '9BWZZZ377VT004251',
    'ano_fabricacao' => 2024,
    'tipo' => 'Caminhonete',
    'combustivel' => 'diesel_s10',
    'secretaria_lotada' => 'Obras',
    'quilometragem_inicial' => 12500,
    'data_aquisicao' => '2026-01-10',
    'documentos_observacoes' => 'CRLV regularizado.',
]);
assertTrue($veiculoCompleto->renavam() === '12345678901', 'RENAVAM deve ser preservado.');
assertTrue($veiculoCompleto->secretariaLotada() === 'Obras', 'Secretaria lotada deve ser preservada.');
assertTrue($veiculoCompleto->quilometragemInicial() === 12500, 'Km inicial deve ser preservada.');

expectException(
    static fn () => new Veiculo('placa-invalida', 'Modelo X', 'disponivel'),
    InvalidPlacaException::class,
    'Placa invalida deveria falhar.'
);

expectException(
    static fn () => new Veiculo('ABC1234', '', 'disponivel'),
    DomainException::class,
    'Modelo vazio deveria falhar.'
);

expectException(
    static fn () => new Veiculo('ABC1D23', 'Modelo', 'disponivel', ['ano_fabricacao' => 1900]),
    DomainException::class,
    'Ano de fabricacao invalido deveria falhar.'
);

$veiculoBaixado = new Veiculo('DEF1G23', 'Caminhao', 'baixado');
expectException(
    static fn () => $veiculoBaixado->enviarParaManutencao(),
    DomainException::class,
    'Veiculo baixado nao deveria voltar para manutencao.'
);

echo "Testes de dominio executados com sucesso." . PHP_EOL;
