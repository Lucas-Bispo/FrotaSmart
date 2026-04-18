<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/AbastecimentoModel.php';
require_once __DIR__ . '/../../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../../backend/models/ParceiroOperacionalModel.php';
require_once __DIR__ . '/../../backend/models/VeiculoModel.php';

secure_session_start();

if (!isset($_SESSION['user']) || !user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de abastecimento.');
    header('Location: /login.php');
    exit;
}

$connection = \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make();
$abastecimentoModel = new AbastecimentoModel($connection);
$veiculoModel = new VeiculoModel($connection);
$motoristaModel = new MotoristaModel($connection);
$parceiroModel = new ParceiroOperacionalModel($connection);

$filtroVeiculoId = isset($_GET['veiculo_id']) ? (int) $_GET['veiculo_id'] : null;
$filtroInicio = trim((string) ($_GET['data_inicio'] ?? ''));
$filtroFim = trim((string) ($_GET['data_fim'] ?? ''));

$abastecimentos = $abastecimentoModel->listByFilters([
    'veiculo_id' => $filtroVeiculoId,
    'data_inicio' => $filtroInicio !== '' ? $filtroInicio : null,
    'data_fim' => $filtroFim !== '' ? $filtroFim : null,
]);
$filtroPeriodo = [
    'data_inicio' => $filtroInicio !== '' ? $filtroInicio : null,
    'data_fim' => $filtroFim !== '' ? $filtroFim : null,
];
$consumoResumo = $abastecimentoModel->getConsumptionSummary($filtroPeriodo);
$rankingEficiencia = $abastecimentoModel->getVehicleEfficiencyRanking(array_merge($filtroPeriodo, ['limit' => 5]));
$parceirosPosto = $parceiroModel->getActiveByTipos(['posto_combustivel', 'prestador_servico']);
$veiculos = $veiculoModel->getAllVeiculos();
$motoristas = $motoristaModel->getAllMotoristas();
$canManage = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingAbastecimento = $editingId > 0 && $canManage ? $abastecimentoModel->findById($editingId) : null;

$totalRegistros = count($abastecimentos);
$totalLitros = 0.0;
$totalValor = 0.0;
$maiorKm = 0;

foreach ($abastecimentos as $abastecimento) {
    $totalLitros += (float) ($abastecimento['litros'] ?? 0);
    $totalValor += (float) ($abastecimento['valor_total'] ?? 0);
    $kmAtual = (int) ($abastecimento['km_atual'] ?? 0);
    if ($kmAtual > $maiorKm) {
        $maiorKm = $kmAtual;
    }
}

$ticketMedio = $totalRegistros > 0 ? $totalValor / $totalRegistros : 0.0;
$consumoMedioPreparado = $totalLitros > 0 && $maiorKm > 0;
$consumoMedioReal = (float) ($consumoResumo['media_consumo_km_l'] ?? 0.0);

$pageTitle = 'Abastecimentos';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Abastecimentos</h1>
        <p class="text-slate-500 text-sm">Controle operacional de combustivel para custo, rastreabilidade e leitura futura de consumo.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">Cada registro vincula veiculo, motorista, km e gasto total.</span>
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

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Registros filtrados</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $totalRegistros; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Litros registrados</p>
        <p class="text-3xl font-bold text-cyan-700 mt-2"><?php echo number_format($totalLitros, 2, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Gasto total</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2">R$ <?php echo number_format($totalValor, 2, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Ticket medio</p>
        <p class="text-3xl font-bold text-amber-600 mt-2">R$ <?php echo number_format($ticketMedio, 2, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Consumo medio</p>
        <p class="text-3xl font-bold text-cyan-700 mt-2"><?php echo $consumoMedioReal > 0 ? number_format($consumoMedioReal, 2, ',', '.') . ' km/L' : '--'; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Alertas de anomalia</p>
        <p class="text-3xl font-bold text-rose-600 mt-2"><?php echo (int) ($consumoResumo['total_alertas'] ?? 0); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1 space-y-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">
                <?php echo $editingAbastecimento ? 'Editar abastecimento' : 'Novo abastecimento'; ?>
            </h2>
            <p class="text-sm text-slate-500 mb-5">Registre o evento com posto, combustivel, litros, valor e hodometro.</p>

            <?php if (empty($veiculos) || empty($motoristas)): ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Cadastre pelo menos um veiculo e um motorista antes de registrar abastecimentos.
                </div>
            <?php elseif ($canManage): ?>
                <form method="POST" action="/abastecimentos.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editingAbastecimento ? 'update_abastecimento' : 'add_abastecimento'; ?>">
                    <?php if ($editingAbastecimento): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $editingAbastecimento['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Veiculo</label>
                        <select name="veiculo_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um veiculo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?php echo (int) $veiculo['id']; ?>" <?php echo ((int) ($editingAbastecimento['veiculo_id'] ?? 0) === (int) $veiculo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $veiculo['placa'] . ' - ' . (string) $veiculo['modelo'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Motorista</label>
                        <select name="motorista_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um motorista</option>
                            <?php foreach ($motoristas as $motorista): ?>
                                <option value="<?php echo (int) $motorista['id']; ?>" <?php echo ((int) ($editingAbastecimento['motorista_id'] ?? 0) === (int) $motorista['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $motorista['nome'] . ' - ' . (string) $motorista['secretaria'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Data</label>
                            <input type="date" name="data_abastecimento" required value="<?php echo htmlspecialchars((string) ($editingAbastecimento['data_abastecimento'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Combustivel</label>
                            <select name="tipo_combustivel" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (['gasolina', 'etanol', 'diesel', 'diesel_s10', 'gnv', 'flex'] as $combustivel): ?>
                                    <option value="<?php echo $combustivel; ?>" <?php echo (($editingAbastecimento['tipo_combustivel'] ?? 'gasolina') === $combustivel) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(strtoupper(str_replace('_', ' ', $combustivel)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Parceiro cadastrado</label>
                        <select name="parceiro_id" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecionar depois</option>
                            <?php foreach ($parceirosPosto as $parceiro): ?>
                                <option value="<?php echo (int) $parceiro['id']; ?>" <?php echo ((int) ($editingAbastecimento['parceiro_id'] ?? 0) === (int) $parceiro['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string) $parceiro['nome_fantasia'] . ' - ' . str_replace('_', ' ', (string) $parceiro['tipo']), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Posto ou fornecedor</label>
                        <input type="text" name="posto" required value="<?php echo htmlspecialchars((string) ($editingAbastecimento['posto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Litros</label>
                            <input type="text" name="litros" required value="<?php echo htmlspecialchars((string) ($editingAbastecimento['litros'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Valor total</label>
                            <input type="text" name="valor_total" required value="<?php echo htmlspecialchars((string) ($editingAbastecimento['valor_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">KM atual</label>
                            <input type="text" name="km_atual" required value="<?php echo htmlspecialchars((string) ($editingAbastecimento['km_atual'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Observacoes</label>
                        <textarea name="observacoes" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[100px]"><?php echo htmlspecialchars((string) ($editingAbastecimento['observacoes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                            <?php echo $editingAbastecimento ? 'Salvar alteracoes' : 'Registrar abastecimento'; ?>
                        </button>
                        <?php if ($editingAbastecimento): ?>
                            <a href="/abastecimentos.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura ao modulo de abastecimento.
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm border border-slate-800">
            <h2 class="text-lg font-semibold mb-3">Leitura gerencial</h2>
            <p class="text-sm text-slate-300">A tela agora cruza km, litros e valor para apoiar leitura automatica de eficiencia e suspeitas.</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-200">
                <li>Consumo medio: <?php echo $consumoMedioReal > 0 ? number_format($consumoMedioReal, 2, ',', '.') . ' km/L' : ($consumoMedioPreparado ? 'aguardando intervalos validos de km' : 'aguardando mais historico de km'); ?></li>
                <li>Gasto por secretaria: preparado via vinculo com motorista e secretaria</li>
                <li>Deteccao de anomalias: <?php echo (int) ($consumoResumo['total_alertas'] ?? 0); ?> registro(s) com atencao automatica</li>
            </ul>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-700">Alertas de abastecimento</h2>
                <p class="text-sm text-slate-500 mt-1 mb-4">Leitura automatica por variacao de km, litros, valor e consumo.</p>

                <?php if (($consumoResumo['top_alertas'] ?? []) === []): ?>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm">
                        Nenhuma anomalia relevante foi identificada no filtro atual.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($consumoResumo['top_alertas'] as $alerta): ?>
                            <div class="rounded-2xl border px-4 py-3 text-sm <?php echo ($alerta['anomalia_status'] ?? '') === 'critico' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-amber-200 bg-amber-50 text-amber-800'; ?>">
                                <div class="font-semibold"><?php echo htmlspecialchars((string) $alerta['placa'] . ' - ' . (string) $alerta['data_abastecimento'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="mt-1 text-xs"><?php echo htmlspecialchars((string) ($alerta['anomalia_resumo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-700">Ranking de eficiencia</h2>
                <p class="text-sm text-slate-500 mt-1 mb-4">Melhores medias de consumo calculadas com base nos intervalos validos de km.</p>

                <?php if ($rankingEficiencia === []): ?>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-600 text-sm">
                        Ainda nao ha dados suficientes para formar ranking de consumo.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($rankingEficiencia as $item): ?>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars((string) $item['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $item['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-cyan-700"><?php echo number_format((float) $item['media_consumo_km_l'], 2, ',', '.'); ?> km/L</div>
                                    <div class="text-xs text-slate-500"><?php echo (int) $item['leituras']; ?> leitura(s)</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-end gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-700">Filtros</h2>
                    <p class="text-sm text-slate-500">Consulte o historico por veiculo e por periodo.</p>
                </div>
                <form method="GET" action="/abastecimentos.php" class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full lg:w-auto">
                    <select name="veiculo_id" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os veiculos</option>
                        <?php foreach ($veiculos as $veiculo): ?>
                            <option value="<?php echo (int) $veiculo['id']; ?>" <?php echo ($filtroVeiculoId !== null && $filtroVeiculoId === (int) $veiculo['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $veiculo['placa'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filtroInicio, ENT_QUOTES, 'UTF-8'); ?>" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filtroFim, ENT_QUOTES, 'UTF-8'); ?>" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 rounded-xl bg-slate-900 px-4 py-3 text-white hover:bg-slate-800">Filtrar</button>
                        <a href="/abastecimentos.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Historico de abastecimentos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Motorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Abastecimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Consumo e alerta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custos</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($abastecimentos)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum abastecimento encontrado para os filtros informados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($abastecimentos as $abastecimento): ?>
                            <tr class="hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $abastecimento['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $abastecimento['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 mt-1">KM <?php echo number_format((float) $abastecimento['km_atual'], 0, ',', '.'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $abastecimento['motorista_nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $abastecimento['secretaria'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $abastecimento['data_abastecimento'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) ($abastecimento['parceiro_nome'] ?? $abastecimento['posto']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs mt-1 inline-flex rounded-full bg-cyan-100 px-2.5 py-1 font-semibold text-cyan-800">
                                        <?php echo htmlspecialchars(strtoupper(str_replace('_', ' ', (string) $abastecimento['tipo_combustivel'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?php if (($abastecimento['consumo_km_l'] ?? null) !== null): ?>
                                        <div><?php echo number_format((float) $abastecimento['consumo_km_l'], 2, ',', '.'); ?> km/L</div>
                                        <div class="text-xs text-slate-500">Percurso: <?php echo number_format((float) ($abastecimento['km_percorrido_desde_anterior'] ?? 0), 0, ',', '.'); ?> km</div>
                                    <?php else: ?>
                                        <div class="text-xs text-slate-400">Aguardando abastecimento anterior valido</div>
                                    <?php endif; ?>

                                    <?php if (!empty($abastecimento['anomalia_resumo'])): ?>
                                        <div class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?php echo ($abastecimento['anomalia_status'] ?? '') === 'critico' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'; ?>">
                                            <?php echo htmlspecialchars(ucfirst((string) $abastecimento['anomalia_status']), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 max-w-xs"><?php echo htmlspecialchars((string) $abastecimento['anomalia_resumo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div class="mt-2 text-xs text-emerald-600">Sem anomalia relevante</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div><?php echo number_format((float) $abastecimento['litros'], 2, ',', '.'); ?> L</div>
                                    <div>R$ <?php echo number_format((float) $abastecimento['valor_total'], 2, ',', '.'); ?></div>
                                    <?php if (($abastecimento['custo_por_litro'] ?? null) !== null): ?>
                                        <div class="text-xs text-slate-500 mt-1">R$ <?php echo number_format((float) $abastecimento['custo_por_litro'], 2, ',', '.'); ?>/L</div>
                                    <?php endif; ?>
                                    <?php if (!empty($abastecimento['observacoes'])): ?>
                                        <div class="text-xs text-slate-500 mt-1 max-w-xs"><?php echo htmlspecialchars((string) $abastecimento['observacoes'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManage): ?>
                                        <a href="/abastecimentos.php?edit=<?php echo (int) $abastecimento['id']; ?>" class="text-blue-600 hover:text-blue-800">
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
