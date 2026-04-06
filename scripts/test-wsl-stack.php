<?php

declare(strict_types=1);

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

use FrotaSmart\Infrastructure\Config\EnvLoader;
use FrotaSmart\Infrastructure\Config\PdoConnectionFactory;

function report(string $label, string $value): void
{
    echo str_pad($label . ':', 20) . $value . PHP_EOL;
}

function ok(string $message): void
{
    echo '[ok] ' . $message . PHP_EOL;
}

function fail(string $message): void
{
    fwrite(STDERR, '[fail] ' . $message . PHP_EOL);
    exit(1);
}

EnvLoader::load($root);

report('PHP binary', PHP_BINARY);
report('PHP version', PHP_VERSION);
report('Project root', $root);
report('DB host', $_ENV['DB_HOST'] ?? '127.0.0.1');
report('DB port', $_ENV['DB_PORT'] ?? '3306');
report('DB name', $_ENV['DB_NAME'] ?? 'frota_smart');
report('DB user', $_ENV['DB_USER'] ?? 'root');

if (! extension_loaded('pdo_mysql')) {
    fail('Extensao pdo_mysql nao esta carregada.');
}

ok('Extensao pdo_mysql carregada.');

try {
    $pdo = PdoConnectionFactory::make();
    $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    report('DB version', $version);
    ok('Conexao PDO validada com sucesso.');
} catch (Throwable $throwable) {
    fail('Falha ao conectar no banco: ' . $throwable->getMessage());
}

$commands = [
    'bootstrap' => 'php scripts/bootstrap-db.php',
    'repository' => 'php scripts/test-repository-pdo.php',
    'manutencao' => 'php scripts/test-manutencao-model.php',
    'operacao_guard' => 'php scripts/test-operacao-frota-guard.php',
    'abastecimento' => 'php scripts/test-abastecimento-model.php',
];

foreach ($commands as $label => $command) {
    report('Executando', $label);
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        fail('Falha em ' . $label . ' com codigo ' . $exitCode . '.');
    }
    ok('Validacao ' . $label . ' concluida.');
}

ok('Stack WSL validada com sucesso.');
