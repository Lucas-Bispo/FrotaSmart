<?php
// Cheque role para menu dinâmico - segurança RBAC
$role = $_SESSION['role'] ?? 'guest';
?>
<nav class="bg-gray-800 text-white w-64 min-h-screen p-4 fixed top-0 left-0">
    <h2 class="text-xl font-bold mb-6">FrotaSmart</h2>
    <ul>
        <li class="mb-4"><a href="dashboard.php" class="hover:bg-gray-700 p-2 rounded block">Dashboard</a></li>
        <li class="mb-4"><a href="veiculos.php" class="hover:bg-gray-700 p-2 rounded block">Veículos</a></li>
        <li class="mb-4"><a href="manutencao.php" class="hover:bg-gray-700 p-2 rounded block">Manutenção</a></li>
        <li class="mb-4"><a href="abastecimento.php" class="hover:bg-gray-700 p-2 rounded block">Abastecimento</a></li>
        <li class="mb-4"><a href="motoristas.php" class="hover:bg-gray-700 p-2 rounded block">Motoristas</a></li>
        <?php if ($role === 'admin' || $role === 'gerente'): ?>
            <li class="mb-4"><a href="relatorios.php" class="hover:bg-gray-700 p-2 rounded block">Relatórios</a></li>
            <li class="mb-4"><a href="usuarios.php" class="hover:bg-gray-700 p-2 rounded block">Usuários</a></li>
        <?php endif; ?>
        <li class="mt-auto"><a href="../../backend/controllers/AuthController.php?logout=1" class="bg-red-500 hover:bg-red-600 p-2 rounded block">Logout</a></li>
    </ul>
</nav>