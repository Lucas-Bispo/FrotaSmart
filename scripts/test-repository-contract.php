<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    fwrite(STDERR, "Autoload nao encontrado. Rode `composer dump-autoload` antes do teste." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

use FrotaSmart\Domain\Repositories\VeiculoRepositoryInterface;

if (! interface_exists(VeiculoRepositoryInterface::class)) {
    throw new RuntimeException('Interface de repositorio de veiculos nao encontrada.');
}

$reflection = new ReflectionClass(VeiculoRepositoryInterface::class);
$expectedMethods = [
    'save',
    'findActiveByPlaca',
    'findAnyByPlaca',
    'existsActiveByPlaca',
    'existsAnyByPlaca',
    'findAll',
    'findArchived',
    'removeByPlaca',
    'restoreByPlaca',
];

foreach ($expectedMethods as $methodName) {
    if (! $reflection->hasMethod($methodName)) {
        throw new RuntimeException(sprintf('Metodo obrigatorio ausente no contrato: %s', $methodName));
    }
}

echo "Contrato de repositorio validado com sucesso." . PHP_EOL;
