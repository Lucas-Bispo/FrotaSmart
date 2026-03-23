<?php
require_once __DIR__ . '/../../backend/config/security.php';
secure_session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../../backend/models/VeiculoModel.php';

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$veiculos = [];
$totalFrota = 0;
$ativos = 0;
$manutencao = 0;
$canManageFleet = in_array($_SESSION['role'] ?? '', ['admin', 'gerente'], true);

try {
    $model = new VeiculoModel();
    $veiculos = $model->getAllVeiculos();
    $totalFrota = count($veiculos);
} catch (Exception $e) {
    error_log('Erro ao carregar dashboard: ' . $e->getMessage());
    $errorMessage = 'Não foi possível carregar os veículos no momento.';
}

foreach ($veiculos as $v) {
    if (($v['status'] ?? '') === 'ativo') {
        $ativos++;
    }

    if (($v['status'] ?? '') === 'manutencao') {
        $manutencao++;
    }
}
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Painel de Controle</h1>
        <p class="text-slate-500 text-sm">Gerenciamento de frotas para prefeituras.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?>.</span>
        <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Perfil: <?php echo htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
        <div class="p-3 bg-blue-500 rounded-xl mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase">Total da Frota</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $totalFrota; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
        <div class="p-3 bg-emerald-500 rounded-xl mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase">Em Operação</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $ativos; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
        <div class="p-3 bg-amber-500 rounded-xl mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase">Manutenção</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $manutencao; ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">Cadastro rápido de veículo</h2>
            <p class="text-sm text-slate-500 mb-5">Adicione um veículo com placa, modelo e status inicial.</p>

            <?php if ($canManageFleet): ?>
                <form method="POST" action="/veiculos.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="add_veiculo">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Placa</label>
                        <input type="text" name="placa" placeholder="ABC1D23" required class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Modelo</label>
                        <input type="text" name="modelo" placeholder="Ex: Mercedes OF-1721" required class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="ativo">Ativo</option>
                            <option value="manutencao">Manutenção</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                        Cadastrar veículo
                    </button>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura à frota.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Frota atualizada</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veículo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($veiculos)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum veículo cadastrado até o momento.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($veiculos as $v): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($v['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($v['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $v['status'] === 'ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($v['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManageFleet): ?>
                                        <form method="POST" action="/veiculos.php" class="inline-flex">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="delete_veiculo">
                                            <input type="hidden" name="id" value="<?php echo (int) $v['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Tem certeza que deseja excluir este veículo?');">Excluir</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-slate-400">Somente leitura</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</main>
</body>
</html>
