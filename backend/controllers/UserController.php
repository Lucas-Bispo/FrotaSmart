<?php
require_once __DIR__ . '/../config/security.php';
secure_session_start();

if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    set_flash('error', 'Acesso negado. Apenas administradores podem gerenciar usuários.');
    header('Location: ../../frontend/views/dashboard.php');
    exit;
}

require_once __DIR__ . '/../models/UserModel.php';

class UserController {
    private $model;
    
    public function __construct() {
        $this->model = new UserModel();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Requisição inválida. Atualize a página e tente novamente.');
                header('Location: ../../frontend/views/user_management.php');
                exit;
            }

            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $role = (string) ($_POST['role'] ?? '');
            $validRoles = ['admin', 'gerente', 'motorista'];

            if ($username === '' || $password === '' || !in_array($role, $validRoles, true)) {
                set_flash('error', 'Todos os campos são obrigatórios e o perfil deve ser válido.');
                header('Location: ../../frontend/views/user_management.php');
                exit;
            }

            if (strlen($username) < 4 || strlen($username) > 50) {
                set_flash('error', 'O nome de usuário deve ter entre 4 e 50 caracteres.');
                header('Location: ../../frontend/views/user_management.php');
                exit;
            }

            if (strlen($password) < 8) {
                set_flash('error', 'A senha deve ter pelo menos 8 caracteres.');
                header('Location: ../../frontend/views/user_management.php');
                exit;
            }

            try {
                $this->model->register($username, $password, $role);
                set_flash('success', "Usuário '{$username}' cadastrado com sucesso.");
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    set_flash('error', 'Nome de usuário já existe.');
                } else {
                    error_log('Erro de cadastro de usuário: ' . $e->getMessage());
                    set_flash('error', 'Erro interno ao cadastrar usuário.');
                }
            }

            header('Location: ../../frontend/views/user_management.php');
            exit;
        }
    }
}

$controller = new UserController();
if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $controller->add();
} else {
    header('Location: ../../frontend/views/user_management.php');
    exit;
}
?>
