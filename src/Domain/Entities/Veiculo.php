<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\Entities;

use FrotaSmart\Domain\Exceptions\DomainException;
use FrotaSmart\Domain\Exceptions\InvalidVeiculoStatusException;
use FrotaSmart\Domain\ValueObjects\Placa;

final class Veiculo
{
    private const STATUS_DISPONIVEL = 'disponivel';
    private const STATUS_EM_MANUTENCAO = 'em_manutencao';
    private const STATUS_EM_VIAGEM = 'em_viagem';
    private const STATUS_BAIXADO = 'baixado';
    private const STATUS_RESERVADO = 'reservado';

    private const LEGACY_STATUS_ALIASES = [
        'ativo' => self::STATUS_DISPONIVEL,
        'manutencao' => self::STATUS_EM_MANUTENCAO,
        'em manutencao' => self::STATUS_EM_MANUTENCAO,
    ];

    private Placa $placa;
    private string $modelo;
    private string $status;

    public function __construct(
        Placa|string $placa,
        string $modelo,
        string $status
    ) {
        $this->placa = $placa instanceof Placa ? $placa : new Placa($placa);
        $this->modelo = $this->normalizeModelo($modelo);
        $this->status = $this->normalizeStatus($status);
    }

    public function placa(): Placa
    {
        return $this->placa;
    }

    public function placaFormatada(): string
    {
        return $this->placa->value();
    }

    public function modelo(): string
    {
        return $this->modelo;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function reservar(): void
    {
        $this->transitionTo(self::STATUS_RESERVADO, [self::STATUS_DISPONIVEL]);
    }

    public function iniciarViagem(): void
    {
        $this->transitionTo(self::STATUS_EM_VIAGEM, [self::STATUS_DISPONIVEL, self::STATUS_RESERVADO]);
    }

    public function enviarParaManutencao(): void
    {
        $this->transitionTo(
            self::STATUS_EM_MANUTENCAO,
            [self::STATUS_DISPONIVEL, self::STATUS_RESERVADO]
        );
    }

    public function liberarParaUso(): void
    {
        $this->transitionTo(
            self::STATUS_DISPONIVEL,
            [self::STATUS_EM_MANUTENCAO, self::STATUS_EM_VIAGEM, self::STATUS_RESERVADO]
        );
    }

    public function baixar(): void
    {
        $this->transitionTo(
            self::STATUS_BAIXADO,
            [self::STATUS_DISPONIVEL, self::STATUS_EM_MANUTENCAO, self::STATUS_RESERVADO]
        );
    }

    public function estaDisponivel(): bool
    {
        return $this->status === self::STATUS_DISPONIVEL;
    }

    public function estaBloqueadoParaUso(): bool
    {
        return in_array($this->status, [self::STATUS_EM_MANUTENCAO, self::STATUS_BAIXADO], true);
    }

    public function descricao(): string
    {
        return sprintf('%s - %s (%s)', $this->placa->value(), $this->modelo, $this->status);
    }

    public static function supportedStatuses(): array
    {
        return [
            self::STATUS_DISPONIVEL,
            self::STATUS_EM_MANUTENCAO,
            self::STATUS_EM_VIAGEM,
            self::STATUS_BAIXADO,
            self::STATUS_RESERVADO,
        ];
    }

    private function normalizeModelo(string $modelo): string
    {
        $modelo = trim($modelo);

        if ($modelo == '') {
            throw new DomainException('Modelo do veiculo e obrigatorio.');
        }

        return $modelo;
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        $status = str_replace('-', '_', $status);

        if (isset(self::LEGACY_STATUS_ALIASES[$status])) {
            $status = self::LEGACY_STATUS_ALIASES[$status];
        }

        if (! in_array($status, self::supportedStatuses(), true)) {
            throw InvalidVeiculoStatusException::unsupportedStatus($status);
        }

        return $status;
    }

    private function transitionTo(string $targetStatus, array $allowedCurrentStatuses): void
    {
        if (! in_array($this->status, $allowedCurrentStatuses, true)) {
            throw InvalidVeiculoStatusException::transitionNotAllowed($this->status, $targetStatus);
        }

        $this->status = $targetStatus;
    }
}
