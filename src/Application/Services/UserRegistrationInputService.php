<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class UserRegistrationInputService
{
    /**
     * @param array<string, mixed> $input
     * @param list<string> $validRoles
     * @return array{username:string,password:string,role:string}
     */
    public function validate(array $input, array $validRoles): array
    {
        $payload = [
            'username' => trim((string) ($input['username'] ?? '')),
            'password' => (string) ($input['password'] ?? ''),
            'role' => (string) ($input['role'] ?? ''),
        ];

        if ($payload['username'] === '' || $payload['password'] === '' || ! in_array($payload['role'], $validRoles, true)) {
            throw new \DomainException('Todos os campos sao obrigatorios e o perfil deve ser valido.');
        }

        if (preg_match('/^[a-zA-Z0-9._-]{4,50}$/', $payload['username']) !== 1) {
            throw new \DomainException('O usuario deve ter entre 4 e 50 caracteres, usando apenas letras, numeros, ponto, underline ou hifen.');
        }

        $password = $payload['password'];
        if (strlen($password) < 12
            || preg_match('/[A-Z]/', $password) !== 1
            || preg_match('/[a-z]/', $password) !== 1
            || preg_match('/[0-9]/', $password) !== 1
            || preg_match('/[^a-zA-Z0-9]/', $password) !== 1) {
            throw new \DomainException('A senha deve ter no minimo 12 caracteres e incluir maiuscula, minuscula, numero e simbolo.');
        }

        return $payload;
    }
}
