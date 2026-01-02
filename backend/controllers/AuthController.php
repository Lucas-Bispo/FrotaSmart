<?php
session_start(); // Inicie sessions para auth
require_once '../models/UserModel.php';

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            $model = new UserModel();
            if ($model->login($username, $password)) {
            $_SESSION['user'] = $username;
            header('Location: ../../frontend/views/dashboard.php');
            exit;
        } else {
            error_log("Login failed for user: " . $username);  // Debug
            $_SESSION['error'] = "Login falhou! Verifique user/senha.";
            header('Location: ../../frontend/views/login.php');
            exit;
            }
        }
    }

    public function logout() {
        session_destroy(); // Destrói toda a session
        header('Location: ../../frontend/views/login.php'); // Redireciona para login
        exit;
    }
}

// Chamada automática baseada em request
$controller = new AuthController();
if (isset($_GET['logout'])) {
    $controller->logout();
} else {
    $controller->login(); // Default para POST de login
}
?>