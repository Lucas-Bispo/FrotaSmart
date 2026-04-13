<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
require_once __DIR__ . '/../../backend/models/RelatorioOperacionalModel.php';
require_once __DIR__ . '/helpers/relatorios_view_helpers.php';

secure_session_start();

if (! isset($_SESSION['user']) || ! user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_READ)) {
    set_flash('error', 'Acesso negado ao modulo de relatorios.');
    header('Location: /login.php');
    exit;
}

$model = new RelatorioOperacionalModel(
    \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make()
);
$report = (string) ($_GET['relatorio'] ?? 'abastecimentos');
$export = (string) ($_GET['export'] ?? '');
$filters = [
    'data_inicio' => (string) ($_GET['data_inicio'] ?? ''),
    'data_fim' => (string) ($_GET['data_fim'] ?? ''),
    'secretaria' => (string) ($_GET['secretaria'] ?? ''),
    'veiculo_id' => (string) ($_GET['veiculo_id'] ?? ''),
    'status' => (string) ($_GET['status'] ?? ''),
    'ator' => (string) ($_GET['ator'] ?? ''),
    'evento' => (string) ($_GET['evento'] ?? ''),
    'tipo_alvo' => (string) ($_GET['tipo_alvo'] ?? ''),
];

$reportLabels = relatorios_report_labels();

if (! isset($reportLabels[$report])) {
    $report = 'abastecimentos';
}

if ($export === 'csv') {
    audit_log('relatorio.exported', [
        'target_id' => $report,
        'report' => $report,
        'filters' => array_filter($filters, static fn (string $value): bool => trim($value) !== ''),
    ]);

    $filename = sprintf('relatorio_%s_%s.csv', $report, date('Ymd_His'));
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $model->exportCsv($report, $filters);
    exit;
}

$secretarias = $model->getSecretarias();
$veiculos = $model->getVeiculos();
$summary = $model->getResumo($filters);
$auditSummary = $model->getAuditSummary($filters);
$auditTargetTypes = $model->getAuditTargetTypes();
$rows = match ($report) {
    'manutencoes' => $model->getManutencaoReport($filters),
    'viagens' => $model->getViagemReport($filters),
    'disponibilidade' => $model->getDisponibilidadeReport($filters),
    'auditoria' => $model->getAuditReport($filters),
    default => $model->getAbastecimentoReport($filters),
};

$statusOptions = relatorios_status_options($report);
$summaryCards = relatorios_summary_cards($report, $summary, $auditSummary);
$tableHeaders = relatorios_table_headers($report);
$filterFieldsMarkup = relatorios_filter_fields_markup(
    $report,
    $filters,
    $secretarias,
    $veiculos,
    $statusOptions,
    $auditTargetTypes
);
$tabs = relatorios_tabs($report, $filters, $reportLabels);
$exportQuery = relatorios_export_query($filters, $report);

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
    <?php foreach ($summaryCards as $card): ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <p class="text-sm font-medium text-slate-500 uppercase"><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-3xl font-bold mt-2 <?php echo htmlspecialchars($card['value_class'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" action="/relatorios.php" class="grid grid-cols-1 md:grid-cols-5 gap-3 flex-1">
            <input type="hidden" name="relatorio" value="<?php echo htmlspecialchars($report, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($filters['data_inicio'], ENT_QUOTES, 'UTF-8'); ?>" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
            <input type="date" name="data_fim" value="<?php echo htmlspecialchars($filters['data_fim'], ENT_QUOTES, 'UTF-8'); ?>" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
            <?php echo $filterFieldsMarkup; ?>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-white hover:bg-slate-800">Aplicar filtros</button>
        </form>

        <div class="flex flex-wrap gap-3">
            <a href="/relatorios.php?<?php echo htmlspecialchars($exportQuery, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl bg-emerald-600 px-4 py-3 text-white hover:bg-emerald-700">
                Exportar CSV
            </a>
            <a href="/relatorios.php?relatorio=<?php echo htmlspecialchars($report, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-xl border border-slate-300 px-4 py-3 text-slate-700 hover:bg-slate-50">
                Limpar
            </a>
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-3 mb-8">
    <?php foreach ($tabs as $tab): ?>
        <a href="<?php echo htmlspecialchars($tab['href'], ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full px-4 py-2 text-sm <?php echo $tab['is_active'] ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-700'; ?>">
            <?php echo htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8'); ?>
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
                    <?php foreach ($tableHeaders as $header): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase"><?php echo htmlspecialchars($header, ENT_QUOTES, 'UTF-8'); ?></th>
                    <?php endforeach; ?>
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
                        <?php echo relatorios_row_markup($report, $row); ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</body>
</html>
