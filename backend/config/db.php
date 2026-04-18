<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';

\FrotaSmart\Infrastructure\Config\EnvLoader::load();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
if ($host === 'localhost') {
    $host = '127.0.0.1';
}

$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'frota_smart';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    if ($e->getCode() == 1049) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;charset=$charset", $user, $pass, $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            $pdo->exec("USE `$dbname`");
        } catch (PDOException $ex) {
            error_log('Erro ao tentar criar o banco de dados: ' . $ex->getMessage());
            $message = is_cli_request()
                ? "Erro ao tentar criar o banco de dados. Consulte os logs.\n"
                : 'Erro interno ao preparar o banco de dados.';
            die($message);
        }
    } else {
        error_log(sprintf(
            'Erro de conexao com o banco [%s] host=%s db=%s user=%s: %s',
            $e->getCode(),
            $host,
            $dbname,
            $user,
            $e->getMessage()
        ));
        $message = is_cli_request()
            ? "Erro de conexao com o banco. Consulte os logs para detalhes.\n"
            : 'Erro interno de conexao com o banco.';
        die($message);
    }
}

function database_connection(): PDO
{
    global $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    throw new RuntimeException('Conexao PDO indisponivel.');
}
