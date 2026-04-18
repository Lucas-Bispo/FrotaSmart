<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../models/ParceiroOperacionalModel.php';

final class ParceiroOperacionalController
{
    private ParceiroOperacionalModel $model;
    private \FrotaSmart\Application\Services\ParceiroOperacionalInputService $inputService;

    public function __construct(
        ?ParceiroOperacionalModel $model = null,
        ?\FrotaSmart\Application\Services\ParceiroOperacionalInputService $inputService = null
    )
    {
        secure_session_start();
        $this->model = $model ?? new ParceiroOperacionalModel();
        $this->inputService = $inputService ?? new \FrotaSmart\Application\Services\ParceiroOperacionalInputService();
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
                'add_parceiro' => $this->create(),
                'update_parceiro' => $this->update(),
                default => $this->flashAndRedirect('error', 'Acao de parceiro nao suportada.'),
            };
        } catch (\DomainException $exception) {
            $this->flashAndRedirect('error', $exception->getMessage());
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->flashAndRedirect('error', 'CNPJ ja cadastrado para outro parceiro.');
            }

            error_log('Erro ao salvar parceiro operacional: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao salvar parceiro operacional.');
        }
    }

    private function create(): void
    {
        $payload = $this->validatedPayload();
        $id = $this->model->create($payload);

        audit_log('parceiro.created', [
            'parceiro_id' => $id,
            'tipo' => $payload['tipo'],
            'nome_fantasia' => $payload['nome_fantasia'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Parceiro operacional cadastrado com sucesso.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->findById($id) === null) {
            $this->flashAndRedirect('error', 'Parceiro operacional nao encontrado para atualizacao.');
        }

        $payload = $this->validatedPayload();
        $this->model->update($id, $payload);

        audit_log('parceiro.updated', [
            'parceiro_id' => $id,
            'tipo' => $payload['tipo'],
            'nome_fantasia' => $payload['nome_fantasia'],
            'status' => $payload['status'],
        ]);

        $this->flashAndRedirect('success', 'Parceiro operacional atualizado com sucesso.');
    }

    /**
     * @return array<string, string|null>
     */
    private function validatedPayload(): array
    {
        return $this->inputService->validate($_POST);
    }

    private function assertCanManage(): void
    {
        if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE)) {
            set_flash('error', 'Acesso negado ao modulo de parceiros operacionais.');
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /parceiros.php');
        exit;
    }
}
