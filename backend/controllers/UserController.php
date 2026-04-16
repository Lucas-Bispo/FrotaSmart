<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';

secure_session_start();
require_same_origin_post();

if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_USERS_MANAGE)) {
    set_flash('error', 'Acesso negado. Apenas administradores podem gerenciar usuarios.');
    header('Location: /dashboard.php');
    exit;
}

require_once __DIR__ . '/../models/UserModel.php';

class UserController
{
    private UserModel $model;
    private \FrotaSmart\Application\Services\UserRegistrationInputService $inputService;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->inputService = new \FrotaSmart\Application\Services\UserRegistrationInputService();
    }

    public function add(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        if (! verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $this->flashAndRedirect('error', 'Requisicao invalida. Atualize a pagina e tente novamente.');
        }

        try {
            $payload = $this->inputService->validate($_POST, valid_roles());
        } catch (\DomainException $exception) {
            $this->flashAndRedirect('error', $exception->getMessage());
        }

        try {
            $this->model->register($payload['username'], $payload['password'], $payload['role']);
            audit_log('user.created', [
                'username' => $payload['username'],
                'role' => $payload['role'],
            ]);

            $this->flashAndRedirect('success', "Usuario '{$payload['username']}' cadastrado com sucesso.");
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->flashAndRedirect('error', 'Nome de usuario ja existe.');
            }

            error_log('Erro de cadastro de usuario: ' . $exception->getMessage());
            $this->flashAndRedirect('error', 'Erro interno ao cadastrar usuario.');
        }
    }

    private function flashAndRedirect(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /user_management.php');
        exit;
    }
}

$controller = new UserController();

if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $controller->add();
} else {
    header('Location: /user_management.php');
    exit;
}
