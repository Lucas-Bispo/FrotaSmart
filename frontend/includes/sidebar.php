<aside class="w-64 bg-gray-800 text-white h-screen fixed left-0 top-0 overflow-y-auto">
    <div class="p-6">
        <h1 class="text-2xl font-bold text-blue-400">FrotaSmart</h1>
        <p class="text-gray-400 text-sm">Gestão Municipal</p>
    </div>
    <nav class="mt-6">
        <a href="../views/dashboard.php" class="block py-3 px-6 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-700 border-l-4 border-blue-500' : ''; ?>">
            Dashboard
        </a>
        <a href="../../backend/controllers/AuthController.php?logout=true" class="block py-3 px-6 hover:bg-red-700 mt-10 text-red-300">
            Sair
        </a>
    </nav>
</aside>