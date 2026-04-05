<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/MotoristaModel.php';

final class MotoristaController
{
    private MotoristaModel $model;

    public function __construct()
    {
        secure_session_start();
        $this->model = new MotoristaModel();
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
                'add_motorista' => $this->create(),
                'update_motorista' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de motorista nao suportada.'),
            };
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->flashAndRedirect('error', 'CPF ou CNH ja cadastrados para outro motorista.');
            }

            error_log('Erro ao salvar motorista: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar motorista.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $this->model->create($payload);
        audit_log('motorista.created', [
            'cpf' => $payload['cpf'],
            'nome' => $payload['nome'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Motorista cadastrado com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Motorista nao encontrado para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);
        audit_log('motorista.updated', [
            'id' => $id,
            'cpf' => $payload['cpf'],
            'nome' => $payload['nome'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Motorista atualizado com sucesso.');
    }

    /**
     * @return array<string, string>
     */
    private function validatedPayload(): array
    {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $cpf = preg_replace('/\D+/', '', (string) ($_POST['cpf'] ?? '')) ?? '';
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $secretaria = trim((string) ($_POST['secretaria'] ?? ''));
        $cnhNumero = preg_replace('/\s+/', '', trim((string) ($_POST['cnh_numero'] ?? ''))) ?? '';
        $cnhCategoria = strtoupper(trim((string) ($_POST['cnh_categoria'] ?? '')));
        $cnhVencimento = (string) ($_POST['cnh_vencimento'] ?? '');
        $status = (string) ($_POST['status'] ?? '');

        if ($nome === '' || mb_strlen($nome) < 3) {
            $this->flashAndRedirect('error', 'Informe um nome valido para o motorista.');
        }

        if (!preg_match('/^\d{11}$/', $cpf)) {
            $this->flashAndRedirect('error', 'Informe um CPF valido com 11 digitos.');
        }

        if ($secretaria === '') {
            $this->flashAndRedirect('error', 'Informe a secretaria de lotacao do motorista.');
        }

        if ($cnhNumero === '' || !preg_match('/^[A-Z0-9]{5,20}$/i', $cnhNumero)) {
            $this->flashAndRedirect('error', 'Informe um numero de CNH valido.');
        }

        if (!in_array($cnhCategoria, ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'], true)) {
            $this->flashAndRedirect('error', 'Informe uma categoria de CNH valida.');
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $cnhVencimento);
        if (!($date instanceof \DateTimeImmutable) || $date->format('Y-m-d') !== $cnhVencimento) {
            $this->flashAndRedirect('error', 'Informe uma data de vencimento valida para a CNH.');
        }

        if (!in_array($status, ['ativo', 'afastado', 'ferias', 'desligado'], true)) {
            $this->flashAndRedirect('error', 'Informe um status operacional valido.');
        }

        return [
            'nome' => $nome,
            'cpf' => $cpf,
            'telefone' => $telefone,
            'secretaria' => $secretaria,
            'cnh_numero' => strtoupper($cnhNumero),
            'cnh_categoria' => $cnhCategoria,
            'cnh_vencimento' => $cnhVencimento,
            'status' => $status,
        ];
    }

    private function assertCanManage(): void
    {
        if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de motoristas.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /motoristas.php');
        exit;
    }
}
