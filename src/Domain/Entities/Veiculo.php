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
    private ?string $renavam;
    private ?string $chassi;
    private ?int $anoFabricacao;
    private ?string $tipo;
    private ?string $combustivel;
    private ?string $secretariaLotada;
    private int $quilometragemInicial;
    private ?string $dataAquisicao;
    private ?string $licenciamentoVencimento;
    private ?string $seguroVencimento;
    private ?string $crlvVencimento;
    private ?string $contratoVencimento;
    private ?string $documentosObservacoes;
    private ?string $arquivadoEm;

    public function __construct(
        Placa|string $placa,
        string $modelo,
        string $status,
        array $dados = []
    ) {
        $this->placa = $placa instanceof Placa ? $placa : new Placa($placa);
        $this->modelo = $this->normalizeModelo($modelo);
        $this->status = $this->normalizeStatus($status);
        $this->renavam = $this->normalizeOptionalDigits($dados['renavam'] ?? null, 9, 20, 'RENAVAM');
        $this->chassi = $this->normalizeOptionalText($dados['chassi'] ?? null, 30, 'Chassi');
        $this->anoFabricacao = $this->normalizeAnoFabricacao($dados['ano_fabricacao'] ?? null);
        $this->tipo = $this->normalizeOptionalText($dados['tipo'] ?? null, 50, 'Tipo');
        $this->combustivel = $this->normalizeOptionalText($dados['combustivel'] ?? null, 30, 'Combustivel');
        $this->secretariaLotada = $this->normalizeOptionalText($dados['secretaria_lotada'] ?? null, 100, 'Secretaria lotada');
        $this->quilometragemInicial = $this->normalizeQuilometragemInicial($dados['quilometragem_inicial'] ?? 0);
        $this->dataAquisicao = $this->normalizeOptionalDate($dados['data_aquisicao'] ?? null, 'Data de aquisicao');
        $this->licenciamentoVencimento = $this->normalizeOptionalDate($dados['licenciamento_vencimento'] ?? null, 'Vencimento do licenciamento');
        $this->seguroVencimento = $this->normalizeOptionalDate($dados['seguro_vencimento'] ?? null, 'Vencimento do seguro');
        $this->crlvVencimento = $this->normalizeOptionalDate($dados['crlv_vencimento'] ?? null, 'Vencimento do CRLV');
        $this->contratoVencimento = $this->normalizeOptionalDate($dados['contrato_vencimento'] ?? null, 'Vencimento do contrato');
        $this->documentosObservacoes = $this->normalizeOptionalText(
            $dados['documentos_observacoes'] ?? null,
            1000,
            'Documentos e observacoes'
        );
        $this->arquivadoEm = $this->normalizeOptionalDateTime($dados['deleted_at'] ?? $dados['arquivado_em'] ?? null);
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

    public function renavam(): ?string
    {
        return $this->renavam;
    }

    public function chassi(): ?string
    {
        return $this->chassi;
    }

    public function anoFabricacao(): ?int
    {
        return $this->anoFabricacao;
    }

    public function tipo(): ?string
    {
        return $this->tipo;
    }

    public function combustivel(): ?string
    {
        return $this->combustivel;
    }

    public function secretariaLotada(): ?string
    {
        return $this->secretariaLotada;
    }

    public function quilometragemInicial(): int
    {
        return $this->quilometragemInicial;
    }

    public function dataAquisicao(): ?string
    {
        return $this->dataAquisicao;
    }

    public function licenciamentoVencimento(): ?string
    {
        return $this->licenciamentoVencimento;
    }

    public function seguroVencimento(): ?string
    {
        return $this->seguroVencimento;
    }

    public function crlvVencimento(): ?string
    {
        return $this->crlvVencimento;
    }

    public function contratoVencimento(): ?string
    {
        return $this->contratoVencimento;
    }

    public function documentosObservacoes(): ?string
    {
        return $this->documentosObservacoes;
    }

    public function arquivadoEm(): ?string
    {
        return $this->arquivadoEm;
    }

    public function estaArquivado(): bool
    {
        return $this->arquivadoEm !== null;
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

    /**
     * @return array<string, int|string|null>
     */
    public function detalhesCadastro(): array
    {
        return [
            'renavam' => $this->renavam,
            'chassi' => $this->chassi,
            'ano_fabricacao' => $this->anoFabricacao,
            'tipo' => $this->tipo,
            'combustivel' => $this->combustivel,
            'secretaria_lotada' => $this->secretariaLotada,
            'quilometragem_inicial' => $this->quilometragemInicial,
            'data_aquisicao' => $this->dataAquisicao,
            'licenciamento_vencimento' => $this->licenciamentoVencimento,
            'seguro_vencimento' => $this->seguroVencimento,
            'crlv_vencimento' => $this->crlvVencimento,
            'contrato_vencimento' => $this->contratoVencimento,
            'documentos_observacoes' => $this->documentosObservacoes,
            'deleted_at' => $this->arquivadoEm,
        ];
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

    private function normalizeOptionalDigits(mixed $value, int $minLength, int $maxLength, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) < $minLength || strlen($digits) > $maxLength) {
            throw new DomainException($field . ' deve ter entre ' . $minLength . ' e ' . $maxLength . ' digitos.');
        }

        return $digits;
    }

    private function normalizeOptionalText(mixed $value, int $maxLength, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) > $maxLength) {
            throw new DomainException($field . ' excede o limite de ' . $maxLength . ' caracteres.');
        }

        return $text;
    }

    private function normalizeAnoFabricacao(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $ano = (int) $value;
        $anoMinimo = 1950;
        $anoMaximo = (int) date('Y') + 1;

        if ($ano < $anoMinimo || $ano > $anoMaximo) {
            throw new DomainException('Ano de fabricacao invalido.');
        }

        return $ano;
    }

    private function normalizeQuilometragemInicial(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (! is_numeric($value)) {
            throw new DomainException('Quilometragem inicial invalida.');
        }

        $quilometragem = (int) $value;

        if ($quilometragem < 0) {
            throw new DomainException('Quilometragem inicial nao pode ser negativa.');
        }

        return $quilometragem;
    }

    private function normalizeOptionalDate(mixed $value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = trim((string) $value);

        if ($date === '') {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if (! $parsed instanceof \DateTimeImmutable || $parsed->format('Y-m-d') !== $date) {
            throw new DomainException($field . ' invalida.');
        }

        return $date;
    }

    private function normalizeOptionalDateTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $dateTime = trim((string) $value);

        if ($dateTime === '') {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTime);

        if (! $parsed instanceof \DateTimeImmutable || $parsed->format('Y-m-d H:i:s') !== $dateTime) {
            throw new DomainException('Data de arquivamento invalida.');
        }

        return $dateTime;
    }
}
