<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
secure_session_start();
require_same_origin_post();

if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_USERS_MANAGE)) {
    set_flash('error', 'Acesso negado. Apenas administradores podem gerenciar usuários.');
    header('Location: /dashboard.php');
    exit;
}

require_once __DIR__ . '/../models/UserModel.php';

class UserController {
    private UserModel $model;

    public function __construct() {
        $this->model = new UserModel();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Requisição inválida. Atualize a página e tente novamente.');
                header('Location: /user_management.php');
                exit;
            }

            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $role = (string) ($_POST['role'] ?? '');
            $validRoles = valid_roles();

            if ($username === '' || $password === '' || !in_array($role, $validRoles, true)) {
                set_flash('error', 'Todos os campos são obrigatórios e o perfil deve ser válido.');
                header('Location: /user_management.php');
                exit;
            }

            if (!$this->isValidUsername($username)) {
                set_flash('error', 'O usuário deve ter entre 4 e 50 caracteres, usando apenas letras, números, ponto, underline ou hífen.');
                header('Location: /user_management.php');
                exit;
            }

            if (!$this->hasStrongPassword($password)) {
                set_flash('error', 'A senha deve ter no mínimo 12 caracteres e incluir maiúscula, minúscula, número e símbolo.');
                header('Location: /user_management.php');
                exit;
            }

            try {
                $this->model->register($username, $password, $role);
                audit_log('user.created', ['username' => $username, 'role' => $role]);
                set_flash('success', "Usuário '{$username}' cadastrado com sucesso.");
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    set_flash('error', 'Nome de usuário já existe.');
                } else {
                    error_log('Erro de cadastro de usuário: ' . $e->getMessage());
                    set_flash('error', 'Erro interno ao cadastrar usuário.');
                }
            }

            header('Location: /user_management.php');
            exit;
        }
    }

    private function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9._-]{4,50}$/', $username) === 1;
    }

    private function hasStrongPassword(string $password): bool
    {
        if (strlen($password) < 12) {
            return false;
        }

        return preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^a-zA-Z0-9]/', $password) === 1;
    }
}

$controller = new UserController();
if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $controller->add();
} else {
    header('Location: /user_management.php');
    exit;
}
