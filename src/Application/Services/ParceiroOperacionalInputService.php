<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class ParceiroOperacionalInputService
{
    private const ALLOWED_TYPES = ['oficina', 'posto_combustivel', 'fornecedor_pecas', 'prestador_servico'];
    private const ALLOWED_STATUS = ['ativo', 'inativo'];

    /**
     * @param array<string, mixed> $input
     * @return array<string, string|null>
     */
    public function validate(array $input): array
    {
        $payload = $this->collectPayload($input);

        $this->assertRequiredName($payload['nome_fantasia']);
        $this->assertCorporateName($payload['razao_social']);
        $this->assertCnpj($payload['cnpj']);
        $this->assertType($payload['tipo']);
        $this->assertStatus($payload['status']);

        return [
            'nome_fantasia' => $payload['nome_fantasia'],
            'razao_social' => $payload['razao_social'],
            'cnpj' => $payload['cnpj'],
            'tipo' => $payload['tipo'],
            'telefone' => $this->nullableText($payload['telefone']),
            'endereco' => $this->nullableText($payload['endereco']),
            'contato_responsavel' => $this->nullableText($payload['contato_responsavel']),
            'status' => $payload['status'],
            'observacoes' => $this->nullableText($payload['observacoes']),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    private function collectPayload(array $input): array
    {
        return [
            'nome_fantasia' => trim((string) ($input['nome_fantasia'] ?? '')),
            'razao_social' => trim((string) ($input['razao_social'] ?? '')),
            'cnpj' => preg_replace('/\D+/', '', (string) ($input['cnpj'] ?? '')) ?? '',
            'tipo' => (string) ($input['tipo'] ?? ''),
            'telefone' => trim((string) ($input['telefone'] ?? '')),
            'endereco' => trim((string) ($input['endereco'] ?? '')),
            'contato_responsavel' => trim((string) ($input['contato_responsavel'] ?? '')),
            'status' => (string) ($input['status'] ?? ''),
            'observacoes' => trim((string) ($input['observacoes'] ?? '')),
        ];
    }

    private function assertRequiredName(string $nomeFantasia): void
    {
        if ($nomeFantasia === '') {
            throw new \DomainException('Informe o nome fantasia do parceiro.');
        }
    }

    private function assertCorporateName(string $razaoSocial): void
    {
        if ($razaoSocial === '') {
            throw new \DomainException('Informe a razao social do parceiro.');
        }
    }

    private function assertCnpj(string $cnpj): void
    {
        if (! preg_match('/^\d{14}$/', $cnpj)) {
            throw new \DomainException('Informe um CNPJ valido com 14 digitos.');
        }
    }

    private function assertType(string $tipo): void
    {
        if (! in_array($tipo, self::ALLOWED_TYPES, true)) {
            throw new \DomainException('Informe um tipo de parceiro valido.');
        }
    }

    private function assertStatus(string $status): void
    {
        if (! in_array($status, self::ALLOWED_STATUS, true)) {
            throw new \DomainException('Informe um status valido para o parceiro.');
        }
    }

    private function nullableText(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }
}
