<?php
require_once __DIR__ . '/../../backend/config/security.php';
secure_session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

if (!user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_USERS_MANAGE)) {
    set_flash('error', 'Acesso negado ao gerenciamento de usuários.');
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'Usuários';
require_once __DIR__ . '/../includes/header.php';

$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Gerenciamento de Usuários</h1>
        <p class="text-slate-500 text-sm">Cadastre perfis administrativos, gestores e motoristas com controle mínimo de acesso.</p>
    </div>
</div>

<?php if ($successMessage): ?>
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700" role="status">
        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700" role="alert">
        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="max-w-2xl bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
    <h2 class="text-xl font-semibold text-slate-700 mb-2">Novo usuário</h2>
    <p class="text-sm text-slate-500 mb-6">As senhas devem ser fortes e exclusivas. Perfis administrativos devem ser concedidos com cautela.</p>

    <form method="POST" action="/users.php" class="space-y-5">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="add_user">

        <div>
            <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Usuário</label>
            <input type="text" id="username" name="username" required minlength="4" maxlength="50" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Senha</label>
            <input type="password" id="password" name="password" required minlength="8" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-slate-700 mb-2">Perfil</label>
            <select id="role" name="role" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                <option value="gerente">Gerente</option>
                <option value="motorista">Motorista</option>
                <option value="auditor">Auditor</option>
                <option value="admin">Administrador</option>
            </select>
        </div>

        <button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-white font-semibold hover:bg-blue-700 transition">
            Cadastrar usuário
        </button>
    </form>
</div>

</main>
</body>
</html>
