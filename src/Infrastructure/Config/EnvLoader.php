<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\Config;

final class EnvLoader
{
    public static function load(?string $projectRoot = null): void
    {
        $projectRoot ??= dirname(__DIR__, 3);
        $envPath = $projectRoot . DIRECTORY_SEPARATOR . '.env';

        if (! is_file($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);

            if ($key === '' || array_key_exists($key, $_ENV)) {
                continue;
            }

            $_ENV[$key] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
}
