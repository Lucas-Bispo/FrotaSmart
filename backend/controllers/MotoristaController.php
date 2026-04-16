<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/MotoristaModel.php';

final class MotoristaController
{
    private MotoristaModel $model;
    private \FrotaSmart\Application\Services\MotoristaInputService $inputService;

    public function __construct()
    {
        secure_session_start();
        $this->model = new MotoristaModel();
        $this->inputService = new \FrotaSmart\Application\Services\MotoristaInputService();
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
        } catch (\DomainException $exception) {
            $this->flashAndRedirect('error', $exception->getMessage());
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
        return $this->inputService->validate($_POST);
    }

    private function assertCanManage(): void
    {
        if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
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
