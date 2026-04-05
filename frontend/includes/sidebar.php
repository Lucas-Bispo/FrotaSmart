<aside class="w-full lg:w-64 bg-slate-900 text-white lg:h-screen lg:fixed left-0 top-0 overflow-y-auto shadow-xl">
    <div class="p-6 border-b border-slate-800">
        <h1 class="text-2xl font-bold text-cyan-400">FrotaSmart</h1>
        <p class="text-slate-400 text-sm">Gestao Municipal</p>
    </div>
    <nav class="mt-2 p-3 space-y-1">
        <a href="/dashboard.php" class="block rounded-xl py-3 px-4 hover:bg-slate-800 <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-slate-800 border border-slate-700' : ''; ?>">
            Dashboard
        </a>
        <?php if (user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)): ?>
            <a href="/motoristas.php" class="block rounded-xl py-3 px-4 hover:bg-slate-800 <?php echo basename($_SERVER['PHP_SELF']) === 'motoristas.php' ? 'bg-slate-800 border border-slate-700' : ''; ?>">
                Motoristas
            </a>
        <?php endif; ?>
        <?php if (user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_USERS_MANAGE)): ?>
            <a href="/user_management.php" class="block rounded-xl py-3 px-4 hover:bg-slate-800 <?php echo basename($_SERVER['PHP_SELF']) === 'user_management.php' ? 'bg-slate-800 border border-slate-700' : ''; ?>">
                Usuarios
            </a>
        <?php endif; ?>
        <form method="POST" action="/auth.php" class="mt-8">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="w-full text-left rounded-xl py-3 px-4 hover:bg-red-700/80 text-red-200">
                Sair
            </button>
        </form>
    </nav>
</aside>
