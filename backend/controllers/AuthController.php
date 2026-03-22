<?php
require_once __DIR__ . '/../config/security.php';
secure_session_start();
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Sessão inválida. Atualize a página e tente novamente.');
                header('Location: ../../frontend/views/login.php');
                exit;
            }

            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($this->isLocked()) {
                $remaining = max(1, ($_SESSION['auth_lock_until'] ?? time()) - time());
                set_flash('error', "Muitas tentativas. Aguarde {$remaining} segundos e tente novamente.");
                set_flash('old_username', $username);
                header('Location: ../../frontend/views/login.php');
                exit;
            }

            if ($username === '' || $password === '') {
                set_flash('error', 'Informe usuário e senha.');
                set_flash('old_username', $username);
                header('Location: ../../frontend/views/login.php');
                exit;
            }

            $model = new UserModel();
            $user = $model->login($username, $password);

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['auth_attempts'] = 0;
                unset($_SESSION['auth_lock_until']);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                set_flash('success', 'Login realizado com sucesso.');
                header('Location: ../../frontend/views/dashboard.php');
                exit;
            } else {
                error_log("Login failed for user: " . $username);
                $this->registerFailedAttempt();
                set_flash('error', 'Login falhou. Verifique suas credenciais.');
                set_flash('old_username', $username);
                header('Location: ../../frontend/views/login.php');
                exit;
            }
        }
    }

    public function logout() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
        header('Location: ../../frontend/views/login.php');
        exit;
    }

    private function registerFailedAttempt(): void
    {
        $_SESSION['auth_attempts'] = ($_SESSION['auth_attempts'] ?? 0) + 1;

        if ($_SESSION['auth_attempts'] >= 5) {
            $_SESSION['auth_lock_until'] = time() + 60;
            $_SESSION['auth_attempts'] = 0;
        }
    }

    private function isLocked(): bool
    {
        if (!isset($_SESSION['auth_lock_until'])) {
            return false;
        }

        if ($_SESSION['auth_lock_until'] <= time()) {
            unset($_SESSION['auth_lock_until']);
            return false;
        }

        return true;
    }
}

$controller = new AuthController();
if (($_SERVER['REQUEST_METHOD'] === 'POST') && (($_POST['action'] ?? '') === 'logout')) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Requisição inválida. Atualize a página e tente novamente.');
        header('Location: ../../frontend/views/dashboard.php');
        exit;
    }
    $controller->logout();
} else {
    $controller->login();
}
?>
