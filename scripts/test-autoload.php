<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(
        STDERR,
        "Autoload nao encontrado. Rode `composer dump-autoload` na raiz do projeto antes deste teste." . PHP_EOL
    );
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Domain\Entities\Veiculo;

$veiculo = new Veiculo('ABC1D23', 'Onibus Escolar', 'disponivel');

echo $veiculo->descricao() . PHP_EOL;
