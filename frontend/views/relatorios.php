<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/RelatorioOperacionalModel.php';

secure_session_start();

if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de relatorios.');
    header('Location: /login.php');
    exit;
}

$model = new RelatorioOperacionalModel();
$report = (string) ($_GET['relatorio'] ?? 'abastecimentos');
$export = (string) ($_GET['export'] ?? '');
$filters = [
    'data_inicio' => (string) ($_GET['data_inicio'] ?? ''),
    'data_fim' => (string) ($_GET['data_fim'] ?? ''),
    'secretaria' => (string) ($_GET['secretaria'] ?? ''),
    'veiculo_id' => (string) ($_GET['veiculo_id'] ?? ''),
    'status' => (string) ($_GET['status'] ?? ''),
];

$reportLabels = [
    'abastecimentos' => 'Abastecimentos',
    'manutencoes' => 'Manutencoes',
    'viagens' => 'Viagens',
    'disponibilidade' => 'Disponibilidade',
];

if (! isset($reportLabels[$report])) {
    $report = 'abastecimentos';
}

if ($export === 'csv') {
    $filename = sprintf('relatorio_%s_%s.csv', $report, date('Ymd_His'));
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $model->exportCsv($report, $filters);
    exit;
}

$secretarias = $model->getSecretarias();
$veiculos = $model->getVeiculos();
$summary = $model->getResumo($filters);
$rows = match ($report) {
    'manutencoes' => $model->getManutencaoReport($filters),
    'viagens' => $model->getViagemReport($filters),
    'disponibilidade' => $model->getDisponibilidadeReport($filters),
    default => $model->getAbastecimentoReport($filters),
};

$statusOptions = match ($report) {
    'abastecimentos' => ['normal' => 'Normal', 'atencao' => 'Atencao', 'critico' => 'Critico'],
    'manutencoes' => ['aberta' => 'Aberta', 'em_andamento' => 'Em andamento', 'concluida' => 'Concluida', 'cancelada' => 'Cancelada'],
    'viagens' => ['em_curso' => 'Em curso', 'concluida' => 'Concluida', 'cancelada' => 'Cancelada'],
    'disponibilidade' => ['ativo' => 'Ativo', 'manutencao' => 'Manutencao', 'em_viagem' => 'Em viagem', 'reservado' => 'Reservado', 'baixado' => 'Baixado'],
    default => [],
};

$pageTitle = 'Relatorios';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Relatorios Operacionais</h1>
        <p class="text-slate-500 text-sm">Leitura gerencial estruturada com filtros cruzados e exportacao inicial em CSV.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Perfil atual: <?php echo htmlspecialchars((string) $_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="text-xs text-slate-500">Use os filtros para consolidar custos, uso e disponibilidade da frota.</span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Gasto abastecimento</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2">R$ <?php echo number_format((float) $summary['gasto_abastecimento'], 2, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Custo manutencao</p>
        <p class="text-3xl font-bold text-amber-600 mt-2">R$ <?php echo number_format((float) $summary['custo_manutencao'], 2, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Viagens / KM</p>
        <p class="text-3xl font-bold text-cyan-700 mt-2"><?php echo (int) $summary['viagens']; ?> / <?php echo number_format((float) $summary['km_viagens'], 0, ',', '.'); ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Veiculos disponiveis</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo (int) $summary['veiculos_disponiveis']; ?></p>
    </div>
</div>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" action="/relatorios.php" class="grid grid-cols-1 md:grid-cols-5 gap-3 flex-1">
            <input type="hidden" name="relatorio" value="<?php echo htmlspecialchars($report, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filters['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
            <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filters['data_fim'], ENT_QUOTES, 'UTF-8'); ?>" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
            <select name="secretaria" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todas as secretarias</option>
                <?php foreach ($secretarias as $secretaria): ?>
                    <option value="<?php echo htmlspecialchars($secretaria, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['secretaria'] === $secretaria ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($secretaria, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="veiculo_id" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos os veiculos</option>
                <?php foreach ($veiculos as $veiculo): ?>
                    <option value="<?php echo (int) $veiculo['id']; ?>" <?php echo $filters['veiculo_id'] === (string) $veiculo['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars((string) $veiculo['placa'] . ' - ' . (string) $veiculo['modelo'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos os status</option>
                <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                    <option value="<?php echo htmlspecialchars($statusValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filters['status'] === $statusValue ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-white hover:bg-slate-800">Aplicar filtros</button>
        </form>

        <div class="flex flex-wrap gap-3">
            <a href="/relatorios.php?<?php echo htmlspecialchars(http_build_query(array_merge($filters, ['relatorio' => $report, 'export' => 'csv'])), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl bg-emerald-600 px-4 py-3 text-white hover:bg-emerald-700">
                Exportar CSV
            </a>
            <a href="/relatorios.php?relatorio=<?php echo htmlspecialchars($report, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                Limpar
            </a>
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-3 mb-8">
    <?php foreach ($reportLabels as $reportKey => $reportLabel): ?>
        <a href="/relatorios.php?<?php echo htmlspecialchars(http_build_query(array_merge($filters, ['relatorio' => $reportKey])), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full px-4 py-2 text-sm <?php echo $report === $reportKey ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-700'; ?>">
            <?php echo htmlspecialchars($reportLabel, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200 bg-slate-50">
        <h2 class="text-lg font-semibold text-slate-700"><?php echo htmlspecialchars($reportLabels[$report], ENT_QUOTES, 'UTF-8'); ?></h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <?php if ($report === 'abastecimentos'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Secretaria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Data e combustivel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Consumo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custos</th>
                    <?php elseif ($report === 'manutencoes'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Secretaria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tipo e periodo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Parceiro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custos</th>
                    <?php elseif ($report === 'viagens'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Secretaria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Motorista</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trajeto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">KM e status</th>
                    <?php else: ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Secretaria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Uso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Historico</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if ($rows === []): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum dado encontrado para os filtros atuais.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $row): ?>
                    <tr class="hover:bg-slate-50 transition align-top">
                        <?php if ($report === 'abastecimentos'): ?>
                            <td class="px-6 py-4"><div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars((string) $row['secretaria'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo htmlspecialchars((string) $row['data_abastecimento'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars(strtoupper(str_replace('_', ' ', (string) $row['tipo_combustivel'])), ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo ($row['consumo_km_l'] ?? null) !== null ? number_format((float) $row['consumo_km_l'], 2, ',', '.') . ' km/L' : '--'; ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) ($row['anomalia_status'] ?? 'normal'), ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div>R$ <?php echo number_format((float) $row['valor_total'], 2, ',', '.'); ?></div><div class="text-xs text-slate-500"><?php echo number_format((float) $row['litros'], 2, ',', '.'); ?> L</div></td>
                        <?php elseif ($report === 'manutencoes'): ?>
                            <td class="px-6 py-4"><div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars((string) ($row['secretaria_lotada'] ?? 'Nao informada'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo htmlspecialchars((string) ucfirst((string) $row['tipo']), ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['data_abertura'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo htmlspecialchars((string) ($row['parceiro_nome'] ?? $row['fornecedor'] ?? 'Nao informado'), ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div>R$ <?php echo number_format((float) (($row['custo_final'] ?? 0) > 0 ? $row['custo_final'] : $row['custo_estimado']), 2, ',', '.'); ?></div></td>
                        <?php elseif ($report === 'viagens'): ?>
                            <td class="px-6 py-4"><div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars((string) $row['secretaria'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars((string) $row['motorista_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo htmlspecialchars((string) $row['origem'], ENT_QUOTES, 'UTF-8'); ?> &rarr; <?php echo htmlspecialchars((string) $row['destino'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['finalidade'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo ($row['km_percorrido'] ?? null) !== null ? number_format((float) $row['km_percorrido'], 0, ',', '.') . ' km' : '--'; ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                        <?php else: ?>
                            <td class="px-6 py-4"><div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars((string) ($row['secretaria_lotada'] ?? 'Nao informada'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $row['situacao_disponibilidade'], ENT_QUOTES, 'UTF-8'); ?></div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div><?php echo (int) $row['total_viagens']; ?> viagem(ns)</div><div class="text-xs text-slate-500"><?php echo (int) $row['total_manutencoes']; ?> manutencao(oes)</div></td>
                            <td class="px-6 py-4 text-sm text-slate-700"><div>Ult. viagem: <?php echo htmlspecialchars((string) ($row['ultima_viagem'] ?? '--'), ENT_QUOTES, 'UTF-8'); ?></div><div class="text-xs text-slate-500">Ult. abastecimento: <?php echo htmlspecialchars((string) ($row['ultimo_abastecimento'] ?? '--'), ENT_QUOTES, 'UTF-8'); ?></div></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</body>
</html>
