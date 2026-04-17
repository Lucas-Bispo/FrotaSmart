<?php

declare(strict_types=1);

require_once __DIR__ . '/MotoristaModel.php';
require_once __DIR__ . '/VeiculoModel.php';
require_once __DIR__ . '/ManutencaoModel.php';

final class OperacaoFrotaGuard
{
    private VeiculoModel $veiculoModel;
    private MotoristaModel $motoristaModel;
    private ManutencaoModel $manutencaoModel;

    public function __construct(
        ?VeiculoModel $veiculoModel = null,
        ?MotoristaModel $motoristaModel = null,
        ?ManutencaoModel $manutencaoModel = null
    )
    {
        $this->veiculoModel = $veiculoModel ?? new VeiculoModel();
        $this->motoristaModel = $motoristaModel ?? new MotoristaModel();
        $this->manutencaoModel = $manutencaoModel ?? new ManutencaoModel();
    }

    /**
     * @return array{blocked:list<string>, warnings:list<string>}
     */
    public function analyzeTrip(int $veiculoId, int $motoristaId, string $dataSaida, int $kmSaida): array
    {
        return $this->analyzeOperation('viagem', $veiculoId, $motoristaId, $dataSaida, $kmSaida);
    }

    /**
     * @return array{blocked:list<string>, warnings:list<string>}
     */
    public function analyzeFuel(int $veiculoId, int $motoristaId, string $dataAbastecimento, int $kmAtual): array
    {
        return $this->analyzeOperation('abastecimento', $veiculoId, $motoristaId, $dataAbastecimento, $kmAtual);
    }

    /**
     * @return array{blocked:list<string>, warnings:list<string>}
     */
    private function analyzeOperation(
        string $tipoOperacao,
        int $veiculoId,
        int $motoristaId,
        string $dataReferencia,
        int $kmReferencia
    ): array {
        $blocked = [];
        $warnings = [];
        $operationDate = $this->normalizeReferenceDate($dataReferencia);

        $veiculo = $this->veiculoModel->findById($veiculoId);
        if ($veiculo === null) {
            $blocked[] = 'O veiculo informado nao foi encontrado.';
        } else {
            $vehicleMessages = $this->analyzeVehicle($tipoOperacao, $veiculo);
            $blocked = array_merge($blocked, $vehicleMessages['blocked']);
            $warnings = array_merge($warnings, $vehicleMessages['warnings']);

            $preventive = $this->manutencaoModel->evaluatePreventiveRuleForVeiculo($veiculoId, $operationDate, $kmReferencia);
            if ($preventive !== null) {
                $summary = trim((string) ($preventive['preventiva_alerta_resumo'] ?? 'Plano preventivo exige revisao.'));
                $status = (string) ($preventive['preventiva_alerta_status'] ?? 'sem_plano');

                if ($status === 'vencida' && $tipoOperacao === 'viagem') {
                    $blocked[] = 'O veiculo possui manutencao preventiva vencida. ' . $summary;
                } elseif (in_array($status, ['vencida', 'proxima'], true)) {
                    $warnings[] = 'Preventiva do veiculo em atencao: ' . $summary;
                }
            }
        }

        $motorista = $this->motoristaModel->findById($motoristaId);
        if ($motorista === null) {
            $blocked[] = 'O motorista informado nao foi encontrado.';
        } else {
            $driverMessages = $this->analyzeDriver($motorista, $operationDate);
            $blocked = array_merge($blocked, $driverMessages['blocked']);
            $warnings = array_merge($warnings, $driverMessages['warnings']);
        }

        return [
            'blocked' => array_values(array_unique($blocked)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    /**
     * @param array<string, mixed> $veiculo
     * @return array{blocked:list<string>, warnings:list<string>}
     */
    private function analyzeVehicle(string $tipoOperacao, array $veiculo): array
    {
        $blocked = [];
        $warnings = [];
        $placa = trim((string) ($veiculo['placa'] ?? 'veiculo'));
        $status = strtolower(trim((string) ($veiculo['status'] ?? '')));

        if (! empty($veiculo['deleted_at'])) {
            $blocked[] = 'O veiculo ' . $placa . ' esta arquivado e nao pode receber novas operacoes.';
        }

        if ($tipoOperacao === 'viagem') {
            if (in_array($status, ['manutencao', 'em_manutencao'], true)) {
                $blocked[] = 'O veiculo ' . $placa . ' esta em manutencao e nao pode iniciar viagem.';
            }

            if ($status === 'em_viagem') {
                $blocked[] = 'O veiculo ' . $placa . ' ja consta como em viagem.';
            }

            if ($status === 'baixado') {
                $blocked[] = 'O veiculo ' . $placa . ' esta baixado e nao pode operar.';
            }
        }

        if ($tipoOperacao === 'abastecimento') {
            if ($status === 'baixado') {
                $blocked[] = 'O veiculo ' . $placa . ' esta baixado e nao pode receber abastecimento operacional.';
            }

            if (in_array($status, ['manutencao', 'em_manutencao'], true)) {
                $warnings[] = 'O veiculo ' . $placa . ' esta em manutencao. Confirme se o abastecimento faz parte do processo autorizado.';
            }
        }

        if ($status === 'reservado') {
            $warnings[] = 'O veiculo ' . $placa . ' esta marcado como reservado.';
        }

        return [
            'blocked' => $blocked,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param array<string, mixed> $motorista
     * @return array{blocked:list<string>, warnings:list<string>}
     */
    private function analyzeDriver(array $motorista, \DateTimeImmutable $operationDate): array
    {
        $blocked = [];
        $warnings = [];
        $nome = trim((string) ($motorista['nome'] ?? 'motorista'));
        $status = strtolower(trim((string) ($motorista['status'] ?? '')));

        if ($status !== 'ativo') {
            $blocked[] = 'O motorista ' . $nome . ' nao esta com status ativo.';
        }

        $cnhDate = \DateTimeImmutable::createFromFormat('Y-m-d', (string) ($motorista['cnh_vencimento'] ?? ''));
        if ($cnhDate instanceof \DateTimeImmutable) {
            if ($cnhDate < $operationDate) {
                $blocked[] = 'A CNH de ' . $nome . ' estava vencida na data informada para a operacao.';
            } elseif ($cnhDate <= $operationDate->modify('+30 days')) {
                $warnings[] = 'A CNH de ' . $nome . ' vence em ' . $cnhDate->format('Y-m-d') . '.';
            }
        }

        return [
            'blocked' => $blocked,
            'warnings' => $warnings,
        ];
    }

    private function normalizeReferenceDate(string $value): \DateTimeImmutable
    {
        $dateOnly = substr(trim($value), 0, 10);
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $dateOnly);

        return $parsed instanceof \DateTimeImmutable ? $parsed : new \DateTimeImmutable('today');
    }
}
