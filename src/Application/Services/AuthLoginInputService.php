<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class AuthLoginInputService
{
    /**
     * @param array<string, mixed> $input
     * @return array{username:string,password:string}
     */
    public function validate(array $input): array
    {
        $payload = [
            'username' => trim((string) ($input['username'] ?? '')),
            'password' => (string) ($input['password'] ?? ''),
        ];

        if ($payload['username'] === '' || $payload['password'] === '') {
            throw new \DomainException('Informe usuario e senha.');
        }

        return $payload;
    }
}
