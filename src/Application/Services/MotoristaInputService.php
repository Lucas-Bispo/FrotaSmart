<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class MotoristaInputService
{
    private const ALLOWED_CNH_CATEGORIES = ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'];
    private const ALLOWED_STATUS = ['ativo', 'afastado', 'ferias', 'desligado'];

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    public function validate(array $input): array
    {
        $payload = $this->collectPayload($input);

        $this->assertName($payload['nome']);
        $this->assertCpf($payload['cpf']);
        $this->assertSecretaria($payload['secretaria']);
        $this->assertCnhNumber($payload['cnh_numero']);
        $this->assertCnhCategory($payload['cnh_categoria']);
        $this->assertCnhExpiration($payload['cnh_vencimento']);
        $this->assertStatus($payload['status']);

        $payload['cnh_numero'] = strtoupper($payload['cnh_numero']);

        return $payload;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    private function collectPayload(array $input): array
    {
        return [
            'nome' => trim((string) ($input['nome'] ?? '')),
            'cpf' => preg_replace('/\D+/', '', (string) ($input['cpf'] ?? '')) ?? '',
            'telefone' => trim((string) ($input['telefone'] ?? '')),
            'secretaria' => trim((string) ($input['secretaria'] ?? '')),
            'cnh_numero' => preg_replace('/\s+/', '', trim((string) ($input['cnh_numero'] ?? ''))) ?? '',
            'cnh_categoria' => strtoupper(trim((string) ($input['cnh_categoria'] ?? ''))),
            'cnh_vencimento' => (string) ($input['cnh_vencimento'] ?? ''),
            'status' => (string) ($input['status'] ?? ''),
        ];
    }

    private function assertName(string $nome): void
    {
        if ($nome === '' || mb_strlen($nome) < 3) {
            throw new \DomainException('Informe um nome valido para o motorista.');
        }
    }

    private function assertCpf(string $cpf): void
    {
        if (! preg_match('/^\d{11}$/', $cpf)) {
            throw new \DomainException('Informe um CPF valido com 11 digitos.');
        }
    }

    private function assertSecretaria(string $secretaria): void
    {
        if ($secretaria === '') {
            throw new \DomainException('Informe a secretaria de lotacao do motorista.');
        }
    }

    private function assertCnhNumber(string $cnhNumero): void
    {
        if ($cnhNumero === '' || ! preg_match('/^[A-Z0-9]{5,20}$/i', $cnhNumero)) {
            throw new \DomainException('Informe um numero de CNH valido.');
        }
    }

    private function assertCnhCategory(string $cnhCategoria): void
    {
        if (! in_array($cnhCategoria, self::ALLOWED_CNH_CATEGORIES, true)) {
            throw new \DomainException('Informe uma categoria de CNH valida.');
        }
    }

    private function assertCnhExpiration(string $cnhVencimento): void
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $cnhVencimento);

        if (! $date instanceof \DateTimeImmutable || $date->format('Y-m-d') !== $cnhVencimento) {
            throw new \DomainException('Informe uma data de vencimento valida para a CNH.');
        }
    }

    private function assertStatus(string $status): void
    {
        if (! in_array($status, self::ALLOWED_STATUS, true)) {
            throw new \DomainException('Informe um status operacional valido.');
        }
    }
}
