<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/ManutencaoModel.php';

final class ManutencaoController
{
    private ManutencaoModel $model;

    public function __construct()
    {
        secure_session_start();
        $this->model = new ManutencaoModel();
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
                'add_manutencao' => $this->create(),
                'update_manutencao' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de manutencao nao suportada.'),
            };
        } catch (PDOException $exception) {
            error_log('Erro ao salvar manutencao: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar manutencao.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('manutencao.created', [
            'manutencao_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'status' => $payload['status'],
            'tipo' => $payload['tipo'],
        ]);

        $this->flashAndRedirect('success', 'Manutencao registrada com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Manutencao nao encontrada para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('manutencao.updated', [
            'manutencao_id' => $id,
            'veiculo_id' => $payload['veiculo_id'],
            'status' => $payload['status'],
            'tipo' => $payload['tipo'],
        ]);

        $this->flashAndRedirect('success', 'Manutencao atualizada com sucesso.');
    }

    private function validatedPayload(): array
    {
        $veiculoId = (int) ($_POST['veiculo_id'] ?? 0);
        $tipo = (string) ($_POST['tipo'] ?? '');
        $status = (string) ($_POST['status'] ?? '');
        $dataAbertura = (string) ($_POST['data_abertura'] ?? '');
        $dataConclusao = trim((string) ($_POST['data_conclusao'] ?? ''));
        $parceiroId = (int) ($_POST['parceiro_id'] ?? 0);
        $fornecedor = trim((string) ($_POST['fornecedor'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $observacoes = trim((string) ($_POST['observacoes'] ?? ''));
        $custoEstimado = trim((string) ($_POST['custo_estimado'] ?? '0'));
        $custoFinal = trim((string) ($_POST['custo_final'] ?? '0'));

        if ($veiculoId <= 0) {
            $this->flashAndRedirect('error', 'Selecione um veiculo valido.');
        }
        if (!in_array($tipo, ['preventiva', 'corretiva'], true)) {
            $this->flashAndRedirect('error', 'Informe um tipo de manutencao valido.');
        }
        if (!in_array($status, ['aberta', 'em_andamento', 'concluida', 'cancelada'], true)) {
            $this->flashAndRedirect('error', 'Informe um status de manutencao valido.');
        }
        if (!$this->isValidDate($dataAbertura)) {
            $this->flashAndRedirect('error', 'Informe uma data de abertura valida.');
        }
        if ($dataConclusao !== '' && !$this->isValidDate($dataConclusao)) {
            $this->flashAndRedirect('error', 'Informe uma data de conclusao valida.');
        }
        if ($descricao === '') {
            $this->flashAndRedirect('error', 'Descreva o motivo ou defeito da manutencao.');
        }

        return [
            'veiculo_id' => $veiculoId,
            'tipo' => $tipo,
            'status' => $status,
            'data_abertura' => $dataAbertura,
            'data_conclusao' => $dataConclusao !== '' ? $dataConclusao : null,
            'parceiro_id' => $parceiroId > 0 ? $parceiroId : null,
            'fornecedor' => $fornecedor !== '' ? $fornecedor : null,
            'custo_estimado' => $this->normalizeDecimal($custoEstimado),
            'custo_final' => $this->normalizeDecimal($custoFinal),
            'descricao' => $descricao,
            'observacoes' => $observacoes !== '' ? $observacoes : null,
        ];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $parsed instanceof \DateTimeImmutable && $parsed->format('Y-m-d') === $date;
    }

    private function normalizeDecimal(string $value): float
    {
        $normalized = str_replace(',', '.', $value);
        if ($normalized === '' || !is_numeric($normalized)) {
            return 0.0;
        }

        return (float) $normalized;
    }

    private function assertCanManage(): void
    {
        if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de manutencao.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /manutencoes.php');
        exit;
    }
}
