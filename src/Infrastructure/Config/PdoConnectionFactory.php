<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Config;

use PDO;

final class PdoConnectionFactory
{
    public static function make(): PDO
    {
        EnvLoader::load();

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        if ($host === 'localhost') {
            $host = '127.0.0.1';
        }

        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? 'frota_smart';
        $user = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        $charset = 'utf8mb4';

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);

        return new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
