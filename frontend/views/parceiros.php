<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/ParceiroOperacionalModel.php';

secure_session_start();

if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de parceiros operacionais.');
    header('Location: /login.php');
    exit;
}

$model = new ParceiroOperacionalModel();
$filtroTipo = trim((string) ($_GET['tipo'] ?? ''));
$filtroStatus = trim((string) ($_GET['status'] ?? ''));
$parceiros = $model->listByFilters([
    'tipo' => $filtroTipo !== '' ? $filtroTipo : null,
    'status' => $filtroStatus !== '' ? $filtroStatus : null,
]);
$canManage = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingParceiro = $editingId > 0 && $canManage ? $model->findById($editingId) : null;

$ativos = 0;
$oficinas = 0;
$postos = 0;

foreach ($parceiros as $parceiro) {
    if (($parceiro['status'] ?? '') === 'ativo') {
        $ativos++;
    }
    if (($parceiro['tipo'] ?? '') === 'oficina') {
        $oficinas++;
    }
    if (($parceiro['tipo'] ?? '') === 'posto_combustivel') {
        $postos++;
    }
}

$pageTitle = 'Parceiros Operacionais';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Parceiros Operacionais</h1>
        <p class="text-slate-500 text-sm">Cadastro central de oficinas, postos, fornecedores de pecas e parceiros da operacao.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">Esse cadastro reduz texto solto e melhora a rastreabilidade do gasto publico.</span>
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

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Registros filtrados</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo count($parceiros); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Ativos</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $ativos; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Oficinas</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $oficinas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Postos</p>
        <p class="text-3xl font-bold text-cyan-700 mt-2"><?php echo $postos; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">
                <?php echo $editingParceiro ? 'Editar parceiro' : 'Novo parceiro'; ?>
            </h2>
            <p class="text-sm text-slate-500 mb-5">Cadastro unico para apoiar manutencao, abastecimento e relatorios futuros.</p>

            <?php if ($canManage): ?>
                <form method="POST" action="/parceiros.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editingParceiro ? 'update_parceiro' : 'add_parceiro'; ?>">
                    <?php if ($editingParceiro): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editingParceiro['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nome fantasia</label>
                        <input type="text" name="nome_fantasia" required value="<?php echo htmlspecialchars((string) ($editingParceiro['nome_fantasia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Razao social</label>
                        <input type="text" name="razao_social" required value="<?php echo htmlspecialchars((string) ($editingParceiro['razao_social'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">CNPJ</label>
                        <input type="text" name="cnpj" required value="<?php echo htmlspecialchars((string) ($editingParceiro['cnpj'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                            <select name="tipo" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['oficina', 'posto_combustivel', 'fornecedor_pecas', 'prestador_servico'] as $tipo): ?>
                                    <option value="<?php echo $tipo; ?>" <?php echo (($editingParceiro['tipo'] ?? 'oficina') === $tipo) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $tipo)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['ativo', 'inativo'] as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo (($editingParceiro['status'] ?? 'ativo') === $status) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Telefone</label>
                        <input type="text" name="telefone" value="<?php echo htmlspecialchars((string) ($editingParceiro['telefone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Endereco</label>
                        <input type="text" name="endereco" value="<?php echo htmlspecialchars((string) ($editingParceiro['endereco'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Contato responsavel</label>
                        <input type="text" name="contato_responsavel" value="<?php echo htmlspecialchars((string) ($editingParceiro['contato_responsavel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Observacoes</label>
                        <textarea name="observacoes" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]"><?php echo htmlspecialchars((string) ($editingParceiro['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                            <?php echo $editingParceiro ? 'Salvar alteracoes' : 'Cadastrar parceiro'; ?>
                        </button>
                        <?php if ($editingParceiro): ?>
                            <a href="/parceiros.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura ao modulo de parceiros.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-700">Filtros</h2>
                    <p class="text-sm text-slate-500">Consulte parceiros por tipo e status operacional.</p>
                </div>
                <form method="GET" action="/parceiros.php" class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full lg:w-auto">
                    <select name="tipo" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os tipos</option>
                        <?php foreach (['oficina', 'posto_combustivel', 'fornecedor_pecas', 'prestador_servico'] as $tipo): ?>
                            <option value="<?php echo $tipo; ?>" <?php echo $filtroTipo === $tipo ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $tipo)), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os status</option>
                        <?php foreach (['ativo', 'inativo'] as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $filtroStatus === $status ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 rounded-xl bg-slate-900 px-4 py-3 text-white hover:bg-slate-800">Filtrar</button>
                        <a href="/parceiros.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Cadastro de parceiros</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Parceiro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($parceiros)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum parceiro operacional encontrado.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($parceiros as $parceiro): ?>
                            <tr class="hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $parceiro['nome_fantasia'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $parceiro['razao_social'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1">CNPJ <?php echo htmlspecialchars((string) $parceiro['cnpj'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $parceiro['tipo'])), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div><?php echo htmlspecialchars((string) ($parceiro['telefone'] ?? 'Nao informado'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars((string) ($parceiro['contato_responsavel'] ?? 'Sem responsavel'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($parceiro['status'] ?? '') === 'ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'; ?>">
                                        <?php echo htmlspecialchars(ucfirst((string) $parceiro['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManage): ?>
                                        <a href="/parceiros.php?edit=<?php echo (int) $parceiro['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                            Editar
                                        </a>
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
