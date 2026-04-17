<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';

secure_session_start();
require_same_origin_post();
require_once __DIR__ . '/../models/UserModel.php';

final class AuthController
{
    private UserModel $model;
    private \FrotaSmart\Application\Services\AuthLoginInputService $inputService;

    public function __construct(
        ?UserModel $model = null,
        ?\FrotaSmart\Application\Services\AuthLoginInputService $inputService = null
    )
    {
        $this->model = $model ?? new UserModel();
        $this->inputService = $inputService ?? new \FrotaSmart\Application\Services\AuthLoginInputService();
    }

    public function login(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        if (! verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $this->redirectToLogin('error', 'Sessao invalida. Atualize a pagina e tente novamente.');
        }

        $username = trim((string) ($_POST['username'] ?? ''));

        if ($this->isLocked()) {
            $remaining = max(1, ((int) ($_SESSION['auth_lock_until'] ?? time())) - time());
            set_flash('old_username', $username);
            audit_log('auth.login.rate_limited', ['username' => $username]);
            $this->redirectToLogin('error', "Muitas tentativas. Aguarde {$remaining} segundos e tente novamente.");
        }

        try {
            $payload = $this->inputService->validate($_POST);
        } catch (\DomainException $exception) {
            set_flash('old_username', $username);
            $this->redirectToLogin('error', $exception->getMessage());
        }

        $user = $this->model->login($payload['username'], $payload['password']);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['auth_attempts'] = 0;
            unset($_SESSION['auth_lock_until']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['session_fingerprint'] = session_fingerprint();
            $_SESSION['last_activity_at'] = time();
            $_SESSION['last_regenerated_at'] = time();
            set_flash('success', 'Login realizado com sucesso.');
            audit_log('auth.login.success', ['username' => $payload['username'], 'role' => $user['role']]);
            header('Location: /dashboard.php');
            exit;
        }

        $this->registerFailedAttempt();
        audit_log('auth.login.failed', ['username' => $payload['username']]);
        set_flash('old_username', $payload['username']);
        $this->redirectToLogin('error', 'Login falhou. Verifique suas credenciais.');
    }

    public function logout(): void
    {
        audit_log('auth.logout');
        destroy_authenticated_session();
        secure_session_start();
        header('Location: /login.php');
        exit;
    }

    private function registerFailedAttempt(): void
    {
        $_SESSION['auth_attempts'] = ((int) ($_SESSION['auth_attempts'] ?? 0)) + 1;

        if ((int) $_SESSION['auth_attempts'] >= 5) {
            $_SESSION['auth_lock_until'] = time() + 60;
            $_SESSION['auth_attempts'] = 0;
        }
    }

    private function isLocked(): bool
    {
        if (! isset($_SESSION['auth_lock_until'])) {
            return false;
        }

        if ((int) $_SESSION['auth_lock_until'] <= time()) {
            unset($_SESSION['auth_lock_until']);
            return false;
        }

        return true;
    }

    private function redirectToLogin(string $level, string $message): void
    {
        set_flash($level, $message);
        header('Location: /login.php');
        exit;
    }
}

$controller = new AuthController();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (($_POST['action'] ?? '') === 'logout')) {
    if (! verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Requisicao invalida. Atualize a pagina e tente novamente.');
        header('Location: /dashboard.php');
        exit;
    }

    $controller->logout();
} else {
    $controller->login();
}
