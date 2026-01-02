<?php
session_start();
// **Controle de Acesso (RBAC Básico): Somente ADMIN pode acessar este controller**
// O seu resumo indica a necessidade de RBAC, então já começamos aqui.
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Acesso negado. Apenas administradores podem gerenciar usuários.";
    header('Location: ../../frontend/views/dashboard.php'); // Redireciona para o dashboard se não for admin
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
            // 1. Validação de segurança
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

            if (empty($username) || empty($password) || empty($role)) {
                $_SESSION['error'] = "Todos os campos são obrigatórios.";
            } else {
                try {
                    $this->model->register($username, $password, $role);
                    $_SESSION['success'] = "Usuário '{$username}' cadastrado com sucesso!";
                } catch (PDOException $e) {
                    // Erro 23000: Violação de Unique Key (Usuário já existe)
                    if ($e->getCode() == '23000') { 
                        $_SESSION['error'] = "Nome de usuário já existe.";
                    } else {
                        error_log("Erro de Cadastro: " . $e->getMessage());
                        $_SESSION['error'] = "Erro interno ao cadastrar usuário.";
                    }
                }
            }
            header('Location: ../../frontend/views/user_management.php');
            exit;
        }
    }
    
    // Futuras funções como getAllUsers, delete, update, etc.
}

// Roteamento Simples
$controller = new UserController();
if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $controller->add();
} else {
    // Redirecionar para o painel de gerenciamento de usuários se não houver ação post
    header('Location: ../../frontend/views/user_management.php');
    exit;
}
?>