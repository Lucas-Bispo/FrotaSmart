<?php
require_once __DIR__ . '/../../backend/config/security.php';
secure_session_start();

if (isset($_SESSION['user'])) {
    header('Location: /dashboard.php');
    exit;
}

$errorMessage = pull_flash('error');
$successMessage = pull_flash('success');
$oldUsername = pull_flash('old_username') ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrotaSmart - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 items-stretch">
        <section class="hidden lg:flex flex-col justify-between rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-cyan-500 text-white p-10 shadow-2xl">
            <div>
                <span class="inline-flex items-center rounded-full bg-white/15 px-4 py-1 text-sm font-medium">Prefeitura • Gestão de Frota</span>
                <h1 class="mt-6 text-4xl font-bold leading-tight">FrotaSmart</h1>
                <p class="mt-4 text-blue-50 text-lg">Controle de veículos, motoristas e viagens em um ambiente seguro para a administração pública.</p>
            </div>

            <div class="space-y-4 text-sm text-blue-50/90">
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                    <p class="font-semibold">Boas práticas já aplicadas</p>
                    <p class="mt-2">Senha com hash, controle de sessão, validação no servidor e trilha inicial de perfis de acesso.</p>
                </div>
                <p>Acesso restrito a usuários autorizados. Em caso de dificuldade, procure o administrador do sistema.</p>
            </div>
        </section>

        <section class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8 lg:p-10">
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Acesso ao sistema</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-800">Entrar</h2>
                <p class="mt-2 text-slate-500">Use suas credenciais institucionais para acessar o painel de gestão da frota.</p>
            </div>

            <?php if ($errorMessage): ?>
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700" role="alert" aria-live="assertive">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700" role="status" aria-live="polite">
                    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth.php" class="space-y-5">
                <?php echo csrf_input(); ?>

                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Usuário</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        placeholder="Digite seu usuário"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-slate-700">Senha</label>
                        <span class="text-xs text-slate-400">Mínimo recomendado: 8 caracteres</span>
                    </div>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 pr-14 text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Digite sua senha"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-3 my-auto h-10 rounded-xl px-3 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Mostrar senha"
                        >
                            Mostrar
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Esqueceu a senha? Solicite redefinição diretamente ao administrador.</p>
                </div>

                <button type="submit" class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-white font-semibold shadow-lg shadow-blue-200 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                    Entrar no painel
                </button>
            </form>
        </section>
    </div>

    <script>
        const togglePasswordButton = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePasswordButton.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            togglePasswordButton.textContent = isPassword ? 'Ocultar' : 'Mostrar';
            togglePasswordButton.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
        });
    </script>
</body>
</html>
