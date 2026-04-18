<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/ManutencaoModel.php';
require_once __DIR__ . '/../../backend/models/ParceiroOperacionalModel.php';
require_once __DIR__ . '/../../backend/models/VeiculoModel.php';

secure_session_start();

if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de manutencoes.');
    header('Location: /login.php');
    exit;
}

$connection = \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make();
$manutencaoModel = new ManutencaoModel($connection);
$parceiroModel = new ParceiroOperacionalModel($connection);
$veiculoModel = new VeiculoModel($connection);

$manutencoes = $manutencaoModel->getAll();
$alertasPreventivos = $manutencaoModel->getPreventiveAlerts();
$parceirosOficina = $parceiroModel->getActiveByTipos(['oficina', 'fornecedor_pecas', 'prestador_servico']);
$veiculos = $veiculoModel->getAllVeiculos();
$canManage = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingManutencao = $editingId > 0 && $canManage ? $manutencaoModel->findById($editingId) : null;

$abertas = 0;
$concluidas = 0;
$emAndamento = 0;
$preventivasVencidas = 0;
$preventivasProximas = 0;

foreach ($manutencoes as $manutencao) {
    if (($manutencao['status'] ?? '') === 'aberta') {
        $abertas++;
    }
    if (($manutencao['status'] ?? '') === 'em_andamento') {
        $emAndamento++;
    }
    if (($manutencao['status'] ?? '') === 'concluida') {
        $concluidas++;
    }
}

foreach ($alertasPreventivos as $alertaPreventivo) {
    if (($alertaPreventivo['preventiva_alerta_status'] ?? '') === 'vencida') {
        $preventivasVencidas++;
    }
    if (($alertaPreventivo['preventiva_alerta_status'] ?? '') === 'proxima') {
        $preventivasProximas++;
    }
}

$pageTitle = 'Manutencoes';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Manutencoes</h1>
        <p class="text-slate-500 text-sm">Historico auditavel de manutencao preventiva e corretiva da frota.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">Registre abertura, andamento e conclusao das intervencoes nos veiculos.</span>
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

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Abertas</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $abertas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Em andamento</p>
        <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $emAndamento; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Concluidas</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $concluidas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Preventivas vencidas</p>
        <p class="text-3xl font-bold text-rose-600 mt-2"><?php echo $preventivasVencidas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Preventivas proximas</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $preventivasProximas; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1">
        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm border border-slate-800 mb-8">
            <h2 class="text-lg font-semibold mb-2">Painel preventivo</h2>
            <p class="text-sm text-slate-300 mb-4">Leitura rapida do que ja venceu ou esta proximo por km e por data.</p>

            <?php if ($alertasPreventivos === []): ?>
                <div class="rounded-2xl border border-emerald-700 bg-emerald-500/10 px-4 py-3 text-emerald-200 text-sm">
                    Nenhuma preventiva critica no momento.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($alertasPreventivos, 0, 5) as $alerta): ?>
                        <div class="rounded-2xl border px-4 py-3 text-sm <?php echo ($alerta['preventiva_alerta_status'] ?? '') === 'vencida' ? 'border-rose-700 bg-rose-500/10 text-rose-100' : 'border-amber-700 bg-amber-500/10 text-amber-100'; ?>">
                            <div class="font-semibold"><?php echo htmlspecialchars((string) $alerta['placa'] . ' - ' . (string) $alerta['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-xs mt-1"><?php echo htmlspecialchars((string) ($alerta['preventiva_alerta_resumo'] ?? 'Sem resumo'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">
                <?php echo $editingManutencao ? 'Editar manutencao' : 'Nova manutencao'; ?>
            </h2>
            <p class="text-sm text-slate-500 mb-5">Cada registro preserva historico, custos, situacao da intervencao e planejamento preventivo futuro.</p>

            <?php if ($canManage): ?>
                <form method="POST" action="/manutencoes.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editingManutencao ? 'update_manutencao' : 'add_manutencao'; ?>">
                    <?php if ($editingManutencao): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editingManutencao['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Veiculo</label>
                        <select name="veiculo_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um veiculo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?php echo (int) $veiculo['id']; ?>" <?php echo ((int) ($editingManutencao['veiculo_id'] ?? 0) === (int) $veiculo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $veiculo['placa'] . ' - ' . (string) $veiculo['modelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                            <select name="tipo" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['preventiva', 'corretiva'] as $tipo): ?>
                                    <option value="<?php echo $tipo; ?>" <?php echo (($editingManutencao['tipo'] ?? 'preventiva') === $tipo) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($tipo); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['aberta', 'em_andamento', 'concluida', 'cancelada'] as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo (($editingManutencao['status'] ?? 'aberta') === $status) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Data de abertura</label>
                            <input type="date" name="data_abertura" required value="<?php echo htmlspecialchars((string) ($editingManutencao['data_abertura'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Data de conclusao</label>
                            <input type="date" name="data_conclusao" value="<?php echo htmlspecialchars((string) ($editingManutencao['data_conclusao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700">Plano preventivo</h3>
                            <p class="text-xs text-slate-500 mt-1">Use para programar proxima revisao por km, por data ou por recorrencia.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">KM de referencia</label>
                                <input type="number" name="km_referencia" min="0" value="<?php echo htmlspecialchars((string) ($editingManutencao['km_referencia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Proxima preventiva por km</label>
                                <input type="number" name="km_proxima_preventiva" min="0" value="<?php echo htmlspecialchars((string) ($editingManutencao['km_proxima_preventiva'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Proxima preventiva por data</label>
                                <input type="date" name="data_proxima_preventiva" value="<?php echo htmlspecialchars((string) ($editingManutencao['data_proxima_preventiva'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Recorrencia em dias</label>
                                <input type="number" name="recorrencia_dias" min="0" value="<?php echo htmlspecialchars((string) ($editingManutencao['recorrencia_dias'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Recorrencia em km</label>
                            <input type="number" name="recorrencia_km" min="0" value="<?php echo htmlspecialchars((string) ($editingManutencao['recorrencia_km'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Parceiro cadastrado</label>
                        <select name="parceiro_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecionar depois</option>
                            <?php foreach ($parceirosOficina as $parceiro): ?>
                                <option value="<?php echo (int) $parceiro['id']; ?>" <?php echo ((int) ($editingManutencao['parceiro_id'] ?? 0) === (int) $parceiro['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $parceiro['nome_fantasia'] . ' - ' . str_replace('_', ' ', (string) $parceiro['tipo']), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fornecedor ou oficina</label>
                        <input type="text" name="fornecedor" value="<?php echo htmlspecialchars((string) ($editingManutencao['fornecedor'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Custo estimado</label>
                            <input type="text" name="custo_estimado" value="<?php echo htmlspecialchars((string) ($editingManutencao['custo_estimado'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Custo final</label>
                            <input type="text" name="custo_final" value="<?php echo htmlspecialchars((string) ($editingManutencao['custo_final'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Descricao do problema</label>
                        <textarea name="descricao" required class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[110px]"><?php echo htmlspecialchars((string) ($editingManutencao['descricao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Observacoes</label>
                        <textarea name="observacoes" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]"><?php echo htmlspecialchars((string) ($editingManutencao['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                            <?php echo $editingManutencao ? 'Salvar alteracoes' : 'Registrar manutencao'; ?>
                        </button>
                        <?php if ($editingManutencao): ?>
                            <a href="/manutencoes.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura ao historico de manutencoes.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Historico de manutencoes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Plano preventivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Fornecedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custos</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($manutencoes)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">Nenhuma manutencao registrada ate o momento.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($manutencoes as $manutencao): ?>
                            <?php
                            $badgeClass = match ((string) $manutencao['status']) {
                                'aberta' => 'bg-amber-100 text-amber-800',
                                'em_andamento' => 'bg-blue-100 text-blue-800',
                                'concluida' => 'bg-emerald-100 text-emerald-800',
                                default => 'bg-slate-200 text-slate-700',
                            };
                            $preventivaBadgeClass = match ((string) ($manutencao['preventiva_alerta_status'] ?? 'sem_plano')) {
                                'vencida' => 'bg-rose-100 text-rose-800',
                                'proxima' => 'bg-amber-100 text-amber-800',
                                'em_dia' => 'bg-emerald-100 text-emerald-800',
                                default => 'bg-slate-100 text-slate-600',
                            };
                            ?>
                            <tr class="hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $manutencao['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $manutencao['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        <?php echo htmlspecialchars((string) $manutencao['data_abertura'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($manutencao['data_conclusao'])): ?>
                                            · ate <?php echo htmlspecialchars((string) $manutencao['data_conclusao'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars(ucfirst((string) $manutencao['tipo']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1 max-w-xs"><?php echo htmlspecialchars((string) $manutencao['descricao'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?php if (($manutencao['tipo'] ?? '') === 'preventiva'): ?>
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $preventivaBadgeClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) ($manutencao['preventiva_alerta_status'] ?? 'sem_plano'))), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <div class="text-xs text-slate-500 mt-2 max-w-xs"><?php echo htmlspecialchars((string) ($manutencao['preventiva_alerta_resumo'] ?? 'Sem resumo'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-xs text-slate-400 mt-1">KM atual: <?php echo number_format((float) ($manutencao['km_atual_veiculo'] ?? 0), 0, ',', '.'); ?></div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Nao se aplica</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div><?php echo htmlspecialchars((string) ($manutencao['parceiro_nome'] ?? $manutencao['fornecedor'] ?? 'Nao informado'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php if (!empty($manutencao['parceiro_tipo'])): ?>
                                        <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $manutencao['parceiro_tipo'])), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $manutencao['status'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div>Estimado: R$ <?php echo number_format((float) $manutencao['custo_estimado'], 2, ',', '.'); ?></div>
                                    <div>Final: R$ <?php echo number_format((float) $manutencao['custo_final'], 2, ',', '.'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManage): ?>
                                        <a href="/manutencoes.php?edit=<?php echo (int) $manutencao['id']; ?>" class="text-blue-600 hover:text-blue-800">
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
