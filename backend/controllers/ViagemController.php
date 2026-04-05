<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/ViagemModel.php';

final class ViagemController
{
    private ViagemModel $model;

    public function __construct()
    {
        secure_session_start();
        $this->model = new ViagemModel();
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $this->assertCanManage();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $this->flashAndRedirect('error', 'Requisicao invalida. Atualize a pagina e tente novamente.');
        }

        $action = (string) ($_POST['action'] ?? '');

        try {
            match ($action) {
                'add_viagem' => $this->create(),
                'update_viagem' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de viagem nao suportada.'),
            };
        } catch (PDOException $exception) {
            error_log('Erro ao salvar viagem: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar viagem.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('viagem.created', [
            'viagem_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'secretaria' => $payload['secretaria'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Viagem registrada com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Viagem nao encontrada para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('viagem.updated', [
            'viagem_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'secretaria' => $payload['secretaria'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Viagem atualizada com sucesso.');
    }

    private function validatedPayload(): array
    {
        $veiculoId = (int) ($_POST['veiculo_id'] ?? 0);
        $motoristaId = (int) ($_POST['motorista_id'] ?? 0);
        $secretaria = trim((string) ($_POST['secretaria'] ?? ''));
        $solicitante = trim((string) ($_POST['solicitante'] ?? ''));
        $origem = trim((string) ($_POST['origem'] ?? ''));
        $destino = trim((string) ($_POST['destino'] ?? ''));
        $finalidade = trim((string) ($_POST['finalidade'] ?? ''));
        $dataSaida = trim((string) ($_POST['data_saida'] ?? ''));
        $dataRetorno = trim((string) ($_POST['data_retorno'] ?? ''));
        $kmSaida = $this->normalizeInteger((string) ($_POST['km_saida'] ?? '0'));
        $kmChegada = $this->normalizeOptionalInteger((string) ($_POST['km_chegada'] ?? ''));
        $status = (string) ($_POST['status'] ?? '');
        $observacoes = trim((string) ($_POST['observacoes'] ?? ''));

        if ($veiculoId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um veiculo valido.');
        }
        if ($motoristaId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um motorista valido.');
        }
        if ($secretaria === '') {
            $this->flashAndRedirect('error', 'Informe a secretaria solicitante.');
        }
        if ($solicitante === '') {
            $this->flashAndRedirect('error', 'Informe o solicitante ou responsavel.');
        }
        if ($origem === '') {
            $this->flashAndRedirect('error', 'Informe a origem da viagem.');
        }
        if ($destino === '') {
            $this->flashAndRedirect('error', 'Informe o destino da viagem.');
        }
        if ($finalidade === '') {
            $this->flashAndRedirect('error', 'Informe a finalidade da viagem.');
        }
        if (!$this->isValidDateTime($dataSaida)) {
            $this->flashAndRedirect('error', 'Informe uma data e hora de saida valida.');
        }
        if ($dataRetorno !== '' && !$this->isValidDateTime($dataRetorno)) {
            $this->flashAndRedirect('error', 'Informe uma data e hora de retorno valida.');
        }
        if ($kmSaida <= 0) {
            $this->flashAndRedirect('error', 'Informe um km inicial valido.');
        }
        if ($kmChegada !== null && $kmChegada < $kmSaida) {
            $this->flashAndRedirect('error', 'O km final nao pode ser menor que o km inicial.');
        }
        if (!in_array($status, ['em_curso', 'concluida', 'cancelada'], true)) {
            $this->flashAndRedirect('error', 'Informe um status de viagem valido.');
        }
        if ($status === 'concluida' && ($dataRetorno === '' || $kmChegada === null)) {
            $this->flashAndRedirect('error', 'Viagens concluidas exigem data de retorno e km final.');
        }

        return [
            'veiculo_id' => $veiculoId,
            'motorista_id' => $motoristaId,
            'secretaria' => $secretaria,
            'solicitante' => $solicitante,
            'origem' => $origem,
            'destino' => $destino,
            'finalidade' => $finalidade,
            'data_saida' => $dataSaida,
            'data_retorno' => $dataRetorno !== '' ? $dataRetorno : null,
            'km_saida' => $kmSaida,
            'km_chegada' => $kmChegada,
            'status' => $status,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }

    private function isValidDateTime(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value);

        return $date instanceof \DateTimeImmutable && $date->format('Y-m-d\TH:i') === $value;
    }

    private function normalizeInteger(string $value): int
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? 0 : (int) $digits;
    }

    private function normalizeOptionalInteger(string $value): ?int
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? null : (int) $digits;
    }

    private function assertCanManage(): void
    {
        if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de viagens.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /viagens.php');
        exit;
    }
}
