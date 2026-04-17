<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/ChecklistOperacionalModel.php';

final class ChecklistOperacionalController
{
    private const ALLOWED_TYPES = ['saida', 'retorno'];
    private const ALLOWED_STATUS = ['conforme', 'nao_conforme', 'pendente'];

    private ChecklistOperacionalModel $model;

    public function __construct()
    {
        secure_session_start();
        $this->model = new ChecklistOperacionalModel();
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $this->assertCanManage();

        if (! verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $this->flashAndRedirect('error', 'Requisicao invalida. Atualize a pagina e tente novamente.');
        }

        $action = (string) ($_POST['action'] ?? '');

        try {
            match ($action) {
                'add_checklist' => $this->create(),
                'update_checklist' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de checklist nao suportada.'),
            };
        } catch (PDOException $exception) {
            error_log('Erro ao salvar checklist operacional: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar checklist operacional.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('checklist.created', [
            'checklist_id' => $id,
            'tipo' => $payload['tipo'],
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'status_conformidade' => $payload['status_conformidade'],
        ]);

        $this->flashAndRedirect('success', 'Checklist operacional registrado com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Checklist operacional nao encontrado para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('checklist.updated', [
            'checklist_id' => $id,
            'tipo' => $payload['tipo'],
            'veiculo_id' => $payload['veiculo_id'],
            'motorista_id' => $payload['motorista_id'],
            'status_conformidade' => $payload['status_conformidade'],
        ]);

        $this->flashAndRedirect('success', 'Checklist operacional atualizado com sucesso.');
    }

    /**
     * @return array<string, int|string|null>
     */
    private function validatedPayload(): array
    {
        $tipo = trim((string) ($_POST['tipo'] ?? ''));
        $veiculoId = (int) ($_POST['veiculo_id'] ?? 0);
        $motoristaId = (int) ($_POST['motorista_id'] ?? 0);
        $secretaria = trim((string) ($_POST['secretaria'] ?? ''));
        $responsavelOperacao = trim((string) ($_POST['responsavel_operacao'] ?? ''));
        $statusConformidade = trim((string) ($_POST['status_conformidade'] ?? ''));
        $realizadoEm = trim((string) ($_POST['realizado_em'] ?? ''));
        $naoConformidades = trim((string) ($_POST['nao_conformidades'] ?? ''));
        $evidenciaReferencia = trim((string) ($_POST['evidencia_referencia'] ?? ''));
        $observacoes = trim((string) ($_POST['observacoes'] ?? ''));
        $aceiteResponsavel = isset($_POST['aceite_responsavel']) ? 1 : 0;

        if (! in_array($tipo, self::ALLOWED_TYPES, true)) {
            $this->flashAndRedirect('error', 'Informe um tipo de checklist valido.');
        }

        if ($veiculoId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um veiculo valido para o checklist.');
        }

        if ($motoristaId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um motorista valido para o checklist.');
        }

        if ($secretaria === '') {
            $this->flashAndRedirect('error', 'Informe a secretaria responsavel pela operacao.');
        }

        if ($responsavelOperacao === '') {
            $this->flashAndRedirect('error', 'Informe o responsavel pela operacao.');
        }

        if (! in_array($statusConformidade, self::ALLOWED_STATUS, true)) {
            $this->flashAndRedirect('error', 'Informe um status de conformidade valido.');
        }

        if (! $this->isValidDateTime($realizadoEm)) {
            $this->flashAndRedirect('error', 'Informe uma data e hora validas para o checklist.');
        }

        if ($statusConformidade === 'nao_conforme' && $naoConformidades === '') {
            $this->flashAndRedirect('error', 'Descreva a nao conformidade identificada.');
        }

        return [
            'tipo' => $tipo,
            'veiculo_id' => $veiculoId,
            'motorista_id' => $motoristaId,
            'secretaria' => $secretaria,
            'responsavel_operacao' => $responsavelOperacao,
            'status_conformidade' => $statusConformidade,
            'aceite_responsavel' => $aceiteResponsavel,
            'realizado_em' => $this->normalizeDateTimeForDatabase($realizadoEm),
            'nao_conformidades' => $naoConformidades !== '' ? $naoConformidades : null,
            'evidencia_referencia' => $evidenciaReferencia !== '' ? $evidenciaReferencia : null,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }

    private function isValidDateTime(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value);

        return $date instanceof \DateTimeImmutable && $date->format('Y-m-d\TH:i') === $value;
    }

    private function normalizeDateTimeForDatabase(string $value): string
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value);

        return $date instanceof \DateTimeImmutable ? $date->format('Y-m-d H:i:s') : $value;
    }

    private function assertCanManage(): void
    {
        if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de checklists operacionais.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /checklists.php');
        exit;
    }
}
