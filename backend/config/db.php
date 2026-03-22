<?php
require_once __DIR__ . '/security.php';

// Carregador simples de variáveis de ambiente (.env)
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue; // Ignora comentários e linhas vazias
        if (strpos($line, '=') === false) continue; // Ignora linhas mal formatadas
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'"); // Remove aspas e espaços
    }
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1'; // 127.0.0.1 força TCP, evitando erro de socket no WSL
// Força o uso de IP se estiver configurado como localhost, resolvendo o erro de socket no WSL
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
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Boa prática
    PDO::ATTR_EMULATE_PREPARES   => false, // Para segurança
];

try {
    // Usa DSN e options para uma conexão mais robusta
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Se o erro for "Unknown database" (código 1049), tenta criar o banco
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
                : "Erro interno ao preparar o banco de dados.";
            die($message);
        }
    } else {
        error_log(sprintf(
            'Erro de conexão com o banco [%s] host=%s db=%s user=%s: %s',
            $e->getCode(),
            $host,
            $dbname,
            $user,
            $e->getMessage()
        ));
        $message = is_cli_request()
            ? "Erro de conexão com o banco. Consulte os logs para detalhes.\n"
            : "Erro interno de conexão com o banco.";
        die($message);
    }
}
?>
