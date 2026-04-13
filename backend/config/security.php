<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

const FROTASMART_SESSION_IDLE_TIMEOUT = 900;
const FROTASMART_SESSION_ROTATE_INTERVAL = 300;

function frotasmart_runtime_path(string $suffix = ''): string
{
    $base = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'runtime';

    if (! is_dir($base)) {
        mkdir($base, 0777, true);
    }

    if ($suffix === '') {
        return $base;
    }

    return $base . DIRECTORY_SEPARATOR . ltrim($suffix, DIRECTORY_SEPARATOR);
}

function apply_security_headers(): void
{
    if (headers_sent() || is_cli_request()) {
        return;
    }

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self' https://cdn.tailwindcss.com 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' https: data:");
}

function secure_session_start(): void
{
    apply_security_headers();

    if (session_status() === PHP_SESSION_ACTIVE) {
        enforce_session_security();
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');

    $sessionPath = frotasmart_runtime_path('sessions');
    if (! is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
    enforce_session_security();
}

function enforce_session_security(): void
{
    $now = time();

    if (isset($_SESSION['last_activity_at']) && ($now - (int) $_SESSION['last_activity_at']) > FROTASMART_SESSION_IDLE_TIMEOUT) {
        destroy_authenticated_session();
        session_start();
        set_flash('error', 'Sua sessão expirou por inatividade. Faça login novamente.');
        return;
    }

    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = $now;
        $_SESSION['last_regenerated_at'] = $now;
    }

    if (isset($_SESSION['user'])) {
        $fingerprint = session_fingerprint();

        if (isset($_SESSION['session_fingerprint']) && !hash_equals($_SESSION['session_fingerprint'], $fingerprint)) {
            destroy_authenticated_session();
            session_start();
            set_flash('error', 'Sessão invalidada por alteração do contexto do cliente.');
            return;
        }

        $_SESSION['session_fingerprint'] = $fingerprint;
    }

    $_SESSION['last_activity_at'] = $now;

    if (($now - (int) ($_SESSION['last_regenerated_at'] ?? 0)) >= FROTASMART_SESSION_ROTATE_INTERVAL) {
        session_regenerate_id(true);
        $_SESSION['last_regenerated_at'] = $now;
    }
}

function session_fingerprint(): string
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown-agent';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown-ip';

    return hash('sha256', $userAgent . '|' . $ip);
}

function destroy_authenticated_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function csrf_token(): string
{
    secure_session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(?string $token): bool
{
    secure_session_start();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function is_same_origin_request(): bool
{
    if (is_cli_request()) {
        return true;
    }

    $requestHost = $_SERVER['HTTP_HOST'] ?? '';
    if ($requestHost === '') {
        return false;
    }

    $requestHostName = strtolower((string) parse_url('http://' . $requestHost, PHP_URL_HOST));
    $requestPort = (int) (parse_url('http://' . $requestHost, PHP_URL_PORT) ?? 0);

    if ($requestHostName === '') {
        return false;
    }

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $header) {
        if (empty($_SERVER[$header])) {
            continue;
        }

        $originHost = parse_url($_SERVER[$header], PHP_URL_HOST);
        if (!is_string($originHost) || $originHost === '') {
            return false;
        }

        $originHost = strtolower($originHost);
        $originPort = (int) (parse_url($_SERVER[$header], PHP_URL_PORT) ?? 0);

        if (!hash_equals($requestHostName, $originHost)) {
            return false;
        }

        if ($originPort !== 0 && $requestPort !== 0 && $originPort !== $requestPort) {
            return false;
        }
    }

    return true;
}

function require_same_origin_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!is_same_origin_request()) {
        http_response_code(403);
        set_flash('error', 'Origem da requisição não autorizada.');
        exit;
    }
}

function set_flash(string $key, string $message): void
{
    secure_session_start();
    $_SESSION['flash'][$key] = $message;
}

function pull_flash(string $key): ?string
{
    secure_session_start();

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function audit_log(string $event, array $context = []): void
{
    try {
        $auditTrail = frotasmart_audit_trail();
        $auditTrail->recordMutation(
            $event,
            infer_audit_action($event),
            infer_audit_target_type($event, $context),
            infer_audit_target_id($event, $context),
            enrich_audit_context($context)
        );
    } catch (Throwable $throwable) {
        error_log('Falha ao registrar auditoria estruturada: ' . $throwable->getMessage());

        $payload = [
            'event' => $event,
            'timestamp' => gmdate(DATE_ATOM),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'user' => $_SESSION['user'] ?? null,
            'context' => $context,
        ];

        error_log('[AUDIT] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

function frotasmart_audit_trail(): \FrotaSmart\Application\Services\AuditTrailService
{
    static $service = null;

    if ($service instanceof \FrotaSmart\Application\Services\AuditTrailService) {
        return $service;
    }

    $service = new \FrotaSmart\Application\Services\AuditTrailService(
        new \FrotaSmart\Infrastructure\Audit\CompositeAuditLogger([
            new \FrotaSmart\Infrastructure\Audit\ErrorLogAuditLogger(),
            new \FrotaSmart\Infrastructure\Audit\PdoAuditLogger(),
        ]),
        new \FrotaSmart\Infrastructure\Audit\RequestAuditContextProvider()
    );

    return $service;
}

function infer_audit_action(string $event): string
{
    $segments = explode('.', $event);
    $suffix = (string) end($segments);

    return match ($suffix) {
        'created' => 'create',
        'updated' => 'update',
        'deleted' => 'delete',
        'archived' => 'archive',
        'restored' => 'restore',
        'success' => 'login',
        'failed' => 'login_failed',
        'logout' => 'logout',
        'rate_limited' => 'rate_limit',
        'exported' => 'export',
        'blocked', 'created_blocked', 'updated_blocked' => 'blocked',
        default => str_replace('-', '_', $suffix),
    };
}

function infer_audit_target_type(string $event, array $context): string
{
    $segments = explode('.', $event);
    $targetType = trim((string) ($segments[0] ?? 'sistema'));

    if ($targetType !== '') {
        return $targetType;
    }

    foreach (['target_type', 'modulo', 'entidade'] as $key) {
        if (isset($context[$key]) && is_string($context[$key]) && $context[$key] !== '') {
            return $context[$key];
        }
    }

    return 'sistema';
}

function infer_audit_target_id(string $event, array $context): string
{
    $candidates = [
        'target_id',
        'placa',
        'placa_anterior',
        'username',
        'veiculo_id',
        'viagem_id',
        'abastecimento_id',
        'motorista_id',
        'parceiro_id',
        'user_id',
        'id',
    ];

    foreach ($candidates as $candidate) {
        if (! array_key_exists($candidate, $context)) {
            continue;
        }

        $value = $context[$candidate];
        if (is_scalar($value) && trim((string) $value) !== '') {
            return trim((string) $value);
        }
    }

    if ($event === 'auth.logout' && isset($_SESSION['user']) && is_string($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    return 'n/a';
}

function enrich_audit_context(array $context): array
{
    $context['actor_role'] ??= $_SESSION['role'] ?? null;
    $context['request_method'] ??= $_SERVER['REQUEST_METHOD'] ?? (is_cli_request() ? 'CLI' : 'GET');
    $context['request_uri'] ??= $_SERVER['REQUEST_URI'] ?? null;

    return $context;
}

function is_cli_request(): bool
{
    return PHP_SAPI === 'cli';
}

function current_user_role(): ?string
{
    return isset($_SESSION['role']) && is_string($_SESSION['role'])
        ? $_SESSION['role']
        : null;
}

function user_can(string $permission, ?string $role = null): bool
{
    $role ??= current_user_role();

    return \FrotaSmart\Application\Security\Rbac::allows($role, $permission);
}

/**
 * @return list<string>
 */
function valid_roles(): array
{
    return \FrotaSmart\Application\Security\Rbac::validRoles();
}
