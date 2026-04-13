<?php

declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/security.php';
secure_session_start();

if (! isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../../backend/models/ManutencaoModel.php';
require_once __DIR__ . '/../../backend/models/AbastecimentoModel.php';
require_once __DIR__ . '/../../backend/models/RelatorioOperacionalModel.php';

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$successMessage = pull_flash('success');
$errorMessage = pull_flash('error');
$veiculos = [];
$veiculosAtivos = [];
$motoristas = [];
$manutencoesRecentes = [];
$abastecimentosRecentes = [];
$painelSecretarias = [];
$painelVeiculos = [];
$abastecimentoModel = null;
$canManageFleet = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_FLEET_MANAGE);
$canManageUsers = user_can(\FrotaSmart\Application\Security\Rbac::PERMISSION_USERS_MANAGE);
$filtroFrota = (string) ($_GET['frota'] ?? 'ativos');

if (! in_array($filtroFrota, ['ativos', 'arquivados', 'todos'], true)) {
    $filtroFrota = 'ativos';
}

$today = new DateTimeImmutable('today');
$alertLimit = $today->modify('+30 days');
$periodoInicio = $today->modify('first day of this month')->format('Y-m-d');
$periodoFim = $today->modify('last day of this month')->format('Y-m-d');

$totalFrota = 0;
$veiculosOperacao = 0;
$veiculosManutencao = 0;
$veiculosArquivados = 0;
$motoristasAtivos = 0;
$cnhsVencendo = 0;
$manutencoesAbertas = 0;
$preventivasVencidas = 0;
$preventivasProximas = 0;
$abastecimentosUltimos7Dias = 0;
$custoOperacionalPeriodo = 0.0;
$consumoMedioPeriodo = 0.0;
$alertasAbastecimento = 0;
$alertasOperacionais = [];

try {
    $veiculoDashboardService = new \FrotaSmart\Application\Services\VeiculoDashboardService(
        new \FrotaSmart\Infrastructure\Persistence\PdoVeiculoRepository(
            \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make()
        )
    );
    $motoristaModel = new MotoristaModel();
    $manutencaoModel = new ManutencaoModel();
    $abastecimentoModel = new AbastecimentoModel();
    $relatorioModel = new RelatorioOperacionalModel();

    $veiculosAtivos = $veiculoDashboardService->listarPorFiltro('ativos');
    $veiculos = $veiculoDashboardService->listarPorFiltro($filtroFrota);
    $motoristas = $motoristaModel->getAllMotoristas();
    $manutencoesRecentes = $manutencaoModel->getRecent(5);
    $abastecimentosRecentes = $abastecimentoModel->getRecent(5);
    $totalFrota = count($veiculosAtivos);
    $veiculosArquivados = $veiculoDashboardService->contarArquivados();
    $manutencoesAbertas = $manutencaoModel->countAbertas();
    $preventivasVencidas = $manutencaoModel->countPreventivasVencidas();
    $preventivasProximas = $manutencaoModel->countPreventivasProximas();
    $custoOperacionalPeriodo = $abastecimentoModel->totalValorPeriodo($periodoInicio, $periodoFim);
    $consumoResumoPeriodo = $abastecimentoModel->getConsumptionSummary($periodoInicio, $periodoFim);
    $consumoMedioPeriodo = (float) ($consumoResumoPeriodo['media_consumo_km_l'] ?? 0.0);
    $alertasAbastecimento = (int) ($consumoResumoPeriodo['total_alertas'] ?? 0);
    $painelSecretarias = $relatorioModel->getExecutiveSummaryBySecretaria($periodoInicio, $periodoFim);
    $painelVeiculos = $relatorioModel->getExecutiveSummaryByVeiculo($periodoInicio, $periodoFim, 8);
} catch (Exception $e) {
    error_log('Erro ao carregar dashboard: ' . $e->getMessage());
    $errorMessage = 'Nao foi possivel carregar os dados do dashboard no momento.';
}

foreach ($veiculosAtivos as $v) {
    $status = strtolower((string) ($v['status'] ?? ''));

    if (in_array($status, ['ativo', 'disponivel', 'em_viagem', 'reservado'], true)) {
        $veiculosOperacao++;
    }

    if (in_array($status, ['manutencao', 'em_manutencao'], true)) {
        $veiculosManutencao++;
    }
}

foreach ($motoristas as $motorista) {
    if (($motorista['status'] ?? '') === 'ativo') {
        $motoristasAtivos++;
    }

    $vencimento = DateTimeImmutable::createFromFormat('Y-m-d', (string) ($motorista['cnh_vencimento'] ?? ''));
    if ($vencimento instanceof DateTimeImmutable && $vencimento >= $today && $vencimento <= $alertLimit) {
        $cnhsVencendo++;
    }
}

foreach ($abastecimentosRecentes as $abastecimento) {
    $data = DateTimeImmutable::createFromFormat('Y-m-d', (string) ($abastecimento['data_abastecimento'] ?? ''));
    if ($data instanceof DateTimeImmutable && $data >= $today->modify('-7 days')) {
        $abastecimentosUltimos7Dias++;
    }
}

if ($veiculosManutencao > 0) {
    $alertasOperacionais[] = $veiculosManutencao . ' veiculo(s) estao em manutencao neste momento.';
}
if ($manutencoesAbertas > 0) {
    $alertasOperacionais[] = $manutencoesAbertas . ' manutencao(oes) seguem abertas ou em andamento.';
}
if ($preventivasVencidas > 0) {
    $alertasOperacionais[] = $preventivasVencidas . ' preventiva(s) estao vencidas e pedem acao imediata.';
}
if ($preventivasProximas > 0) {
    $alertasOperacionais[] = $preventivasProximas . ' preventiva(s) entram em janela de atencao nos proximos dias ou kms.';
}
if ($alertasAbastecimento > 0) {
    $alertasOperacionais[] = $alertasAbastecimento . ' abastecimento(s) apresentam anomalias de consumo ou custo no periodo.';
}
if ($cnhsVencendo > 0) {
    $alertasOperacionais[] = $cnhsVencendo . ' CNH(s) vencem nos proximos 30 dias.';
}
if ($veiculosArquivados > 0) {
    $alertasOperacionais[] = $veiculosArquivados . ' veiculo(s) seguem arquivados e disponiveis para consulta ou restauracao.';
}

/**
 * @param string $status
 */
function dashboard_vehicle_status_label(string $status): string
{
    return match ($status) {
        'ativo' => 'Disponivel',
        'manutencao' => 'Em manutencao',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

/**
 * @param string $status
 */
function dashboard_vehicle_status_badge(string $status): string
{
    return match ($status) {
        'ativo', 'disponivel' => 'bg-emerald-100 text-emerald-800',
        'manutencao', 'em_manutencao' => 'bg-amber-100 text-amber-800',
        'em_viagem' => 'bg-blue-100 text-blue-800',
        'reservado' => 'bg-purple-100 text-purple-800',
        'baixado' => 'bg-slate-300 text-slate-700',
        default => 'bg-slate-200 text-slate-700',
    };
}

function dashboard_vehicle_filter_label(string $filtro): string
{
    return match ($filtro) {
        'arquivados' => 'somente arquivados',
        'todos' => 'ativos e arquivados',
        default => 'somente ativos',
    };
}

function dashboard_executive_alert_badge(string $status): string
{
    return match ($status) {
        'vencida' => 'bg-rose-100 text-rose-800',
        'proxima' => 'bg-amber-100 text-amber-800',
        'em_dia' => 'bg-emerald-100 text-emerald-800',
        default => 'bg-slate-100 text-slate-700',
    };
}

function dashboard_executive_alert_label(string $status): string
{
    return match ($status) {
        'vencida' => 'Preventiva vencida',
        'proxima' => 'Preventiva proxima',
        'em_dia' => 'Preventiva em dia',
        default => 'Sem plano',
    };
}

$secretariasMonitoradas = count($painelSecretarias);
$topSecretaria = $painelSecretarias[0] ?? null;
$topVeiculoExecutivo = $painelVeiculos[0] ?? null;
?>
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Dashboard Operacional</h1>
        <p class="text-slate-500 text-sm">Visao gerencial da frota municipal com alertas, custos e prioridades do dia.</p>
    </div>
    <div class="text-left lg:text-right">
        <span class="block text-sm font-medium text-slate-700">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?>.</span>
        <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Perfil: <?php echo htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="block text-xs text-slate-500 mt-1">Periodo de custo: <?php echo htmlspecialchars($periodoInicio, ENT_QUOTES, 'UTF-8'); ?> ate <?php echo htmlspecialchars($periodoFim, ENT_QUOTES, 'UTF-8'); ?></span>
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

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
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
            <p class="text-sm font-medium text-slate-500 uppercase">Em Operacao</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $veiculosOperacao; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
        <div class="p-3 bg-amber-500 rounded-xl mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase">Manutencao</p>
            <p class="text-2xl font-bold text-slate-800"><?php echo $veiculosManutencao; ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
        <div class="p-3 bg-cyan-600 rounded-xl mr-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m3 0h6M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase">Custo do Periodo</p>
            <p class="text-2xl font-bold text-slate-800">R$ <?php echo number_format($custoOperacionalPeriodo, 2, ',', '.'); ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-8 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Manutencoes abertas</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $manutencoesAbertas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Abastecimentos em 7 dias</p>
        <p class="text-3xl font-bold text-cyan-700 mt-2"><?php echo $abastecimentosUltimos7Dias; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Motoristas ativos</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2"><?php echo $motoristasAtivos; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">CNHs vencendo</p>
        <p class="text-3xl font-bold text-rose-600 mt-2"><?php echo $cnhsVencendo; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Prev. vencidas</p>
        <p class="text-3xl font-bold text-rose-700 mt-2"><?php echo $preventivasVencidas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Prev. proximas</p>
        <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo $preventivasProximas; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Consumo medio</p>
        <p class="text-3xl font-bold text-cyan-700 mt-2"><?php echo $consumoMedioPeriodo > 0 ? number_format($consumoMedioPeriodo, 2, ',', '.') : '--'; ?></p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Arquivados</p>
        <p class="text-3xl font-bold text-slate-700 mt-2"><?php echo $veiculosArquivados; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Secretarias monitoradas</p>
        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $secretariasMonitoradas; ?></p>
        <p class="text-xs text-slate-500 mt-2">Consolidacao de frota, viagens, custo e alertas no periodo.</p>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Secretaria com maior custo</p>
        <?php if (is_array($topSecretaria)): ?>
            <p class="text-xl font-bold text-slate-800 mt-2"><?php echo htmlspecialchars((string) $topSecretaria['secretaria'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-sm text-slate-600 mt-1">R$ <?php echo number_format((float) ($topSecretaria['custo_total_periodo'] ?? 0), 2, ',', '.'); ?> no periodo</p>
        <?php else: ?>
            <p class="text-xl font-bold text-slate-800 mt-2">--</p>
            <p class="text-sm text-slate-500 mt-1">Sem custo consolidado ate o momento.</p>
        <?php endif; ?>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <p class="text-sm font-medium text-slate-500 uppercase">Veiculo mais sensivel</p>
        <?php if (is_array($topVeiculoExecutivo)): ?>
            <p class="text-xl font-bold text-slate-800 mt-2"><?php echo htmlspecialchars((string) $topVeiculoExecutivo['placa'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-sm text-slate-600 mt-1"><?php echo (int) ($topVeiculoExecutivo['total_alertas'] ?? 0); ?> alerta(s) e R$ <?php echo number_format((float) ($topVeiculoExecutivo['custo_total_periodo'] ?? 0), 2, ',', '.'); ?></p>
        <?php else: ?>
            <p class="text-xl font-bold text-slate-800 mt-2">--</p>
            <p class="text-sm text-slate-500 mt-1">Sem consolidacao por veiculo no periodo.</p>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-1 space-y-8">
        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm border border-slate-800">
            <h2 class="text-lg font-semibold mb-2">Alertas operacionais</h2>
            <p class="text-sm text-slate-300 mb-4">Itens que merecem olhar mais rapido na rotina da frota.</p>

            <?php if ($alertasOperacionais === []): ?>
                <div class="rounded-2xl border border-emerald-700 bg-emerald-500/10 px-4 py-3 text-emerald-200 text-sm">
                    Nenhum alerta critico no momento. O painel segue estavel.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($alertasOperacionais as $alerta): ?>
                        <div class="rounded-2xl border border-amber-700 bg-amber-500/10 px-4 py-3 text-amber-100 text-sm">
                            <?php echo htmlspecialchars($alerta, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">Acoes rapidas</h2>
            <p class="text-sm text-slate-500 mb-5">Atalhos para os fluxos mais frequentes da operacao.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="/motoristas.php" class="rounded-2xl border border-slate-200 px-4 py-4 hover:border-cyan-300 hover:bg-cyan-50 transition">
                    <span class="block text-sm font-semibold text-slate-800">Motoristas</span>
                    <span class="block text-xs text-slate-500 mt-1">Cadastro e situacao das CNHs</span>
                </a>
                <a href="/manutencoes.php" class="rounded-2xl border border-slate-200 px-4 py-4 hover:border-amber-300 hover:bg-amber-50 transition">
                    <span class="block text-sm font-semibold text-slate-800">Manutencoes</span>
                    <span class="block text-xs text-slate-500 mt-1">Abertura e acompanhamento das OS</span>
                </a>
                <a href="/abastecimentos.php" class="rounded-2xl border border-slate-200 px-4 py-4 hover:border-emerald-300 hover:bg-emerald-50 transition">
                    <span class="block text-sm font-semibold text-slate-800">Abastecimentos</span>
                    <span class="block text-xs text-slate-500 mt-1">Registro de combustivel e custo</span>
                </a>
                <?php if ($canManageUsers): ?>
                    <a href="/user_management.php" class="rounded-2xl border border-slate-200 px-4 py-4 hover:border-slate-400 hover:bg-slate-50 transition">
                        <span class="block text-sm font-semibold text-slate-800">Usuarios</span>
                        <span class="block text-xs text-slate-500 mt-1">Perfis e acessos do sistema</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">Cadastro consolidado de veiculo</h2>
            <p class="text-sm text-slate-500 mb-5">Base do ciclo 03 com identificacao, lotacao e dados iniciais de operacao da frota.</p>

            <?php if ($canManageFleet): ?>
                <form method="POST" action="/veiculos.php" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="add_veiculo">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Placa</label>
                            <input type="text" name="placa" placeholder="ABC1D23" required class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Modelo</label>
                            <input type="text" name="modelo" placeholder="Ex: Mercedes Sprinter" required class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RENAVAM</label>
                            <input type="text" name="renavam" placeholder="Opcional" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Chassi</label>
                            <input type="text" name="chassi" placeholder="Opcional" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Secretaria lotada</label>
                            <input type="text" name="secretaria_lotada" placeholder="Ex: Saude" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="disponivel">Disponivel</option>
                                <option value="em_manutencao">Em manutencao</option>
                                <option value="reservado">Reservado</option>
                                <option value="em_viagem">Em viagem</option>
                                <option value="baixado">Baixado</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                            <input type="text" name="tipo" placeholder="Ex: Van, Caminhonete, Caminhao" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Combustivel</label>
                            <select name="combustivel" class="w-full border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione</option>
                                <option value="gasolina">Gasolina</option>
                                <option value="etanol">Etanol</option>
                                <option value="diesel">Diesel</option>
                                <option value="diesel_s10">Diesel S10</option>
                                <option value="flex">Flex</option>
                                <option value="gnv">GNV</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Ano</label>
                            <input type="number" name="ano_fabricacao" min="1950" max="<?php echo (int) date('Y') + 1; ?>" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Km inicial</label>
                            <input type="number" name="quilometragem_inicial" min="0" value="0" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Data de aquisicao</label>
                            <input type="date" name="data_aquisicao" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Documentos e observacoes</label>
                        <textarea name="documentos_observacoes" rows="3" placeholder="CRLV, numero de apolice, observacoes iniciais..." class="w-full border border-slate-300 rounded-xl p-3 focus:ring-blue-500 focus:border-blue-500 outline-none"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition duration-200">
                        Cadastrar veiculo
                    </button>
                </form>
            <?php else: ?>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                    Seu perfil possui acesso somente leitura a frota.
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold mb-2 text-slate-700">Painel executivo por secretaria</h2>
            <p class="text-sm text-slate-500 mb-5">Leitura consolidada de disponibilidade, uso, custo e risco por orgao no periodo atual.</p>

            <div class="space-y-3">
                <?php if ($painelSecretarias === []): ?>
                    <p class="text-sm text-slate-500">Ainda nao ha massa suficiente para o painel executivo por secretaria.</p>
                <?php endif; ?>

                <?php foreach (array_slice($painelSecretarias, 0, 6) as $secretariaResumo): ?>
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars((string) $secretariaResumo['secretaria'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Frota ativa: <?php echo (int) $secretariaResumo['frota_ativa']; ?> |
                                    Em operacao: <?php echo (int) $secretariaResumo['frota_operacao']; ?> |
                                    Motoristas ativos: <?php echo (int) $secretariaResumo['motoristas_ativos']; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate-900">R$ <?php echo number_format((float) $secretariaResumo['custo_total_periodo'], 2, ',', '.'); ?></p>
                                <p class="text-xs text-slate-500">custo total</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <span class="block text-slate-500 text-xs">Viagens / KM</span>
                                <span class="font-semibold text-slate-800"><?php echo (int) $secretariaResumo['viagens_periodo']; ?> / <?php echo number_format((float) $secretariaResumo['km_viagens_periodo'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <span class="block text-slate-500 text-xs">Disponibilidade</span>
                                <span class="font-semibold text-slate-800"><?php echo $secretariaResumo['disponibilidade_percentual'] !== null ? number_format((float) $secretariaResumo['disponibilidade_percentual'], 1, ',', '.') . '%' : '--'; ?></span>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <span class="block text-slate-500 text-xs">Abastecimento</span>
                                <span class="font-semibold text-slate-800"><?php echo (int) $secretariaResumo['abastecimentos_periodo']; ?> registro(s)</span>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <span class="block text-slate-500 text-xs">Alertas</span>
                                <span class="font-semibold <?php echo ((int) $secretariaResumo['alertas_total'] > 0) ? 'text-amber-700' : 'text-emerald-700'; ?>"><?php echo (int) $secretariaResumo['alertas_total']; ?> alerta(s)</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Painel executivo por veiculo</h2>
                <p class="text-sm text-slate-500 mt-1">Veiculos mais sensiveis no periodo, combinando custo, uso e alertas operacionais.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Uso no periodo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Alertas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if ($painelVeiculos === []): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Ainda nao ha leitura executiva suficiente por veiculo.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($painelVeiculos as $veiculoResumo): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $veiculoResumo['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $veiculoResumo['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-400 mt-1"><?php echo htmlspecialchars((string) $veiculoResumo['secretaria_lotada'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div><?php echo (int) $veiculoResumo['viagens_periodo']; ?> viagem(ns)</div>
                                    <div class="text-xs text-slate-500"><?php echo number_format((float) $veiculoResumo['km_viagens_periodo'], 0, ',', '.'); ?> km</div>
                                    <div class="text-xs text-slate-500"><?php echo (int) $veiculoResumo['abastecimentos_periodo']; ?> abastecimento(s)</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div>R$ <?php echo number_format((float) $veiculoResumo['custo_total_periodo'], 2, ',', '.'); ?></div>
                                    <div class="text-xs text-slate-500">Abast.: R$ <?php echo number_format((float) $veiculoResumo['gasto_abastecimento_periodo'], 2, ',', '.'); ?></div>
                                    <div class="text-xs text-slate-500">Manut.: R$ <?php echo number_format((float) $veiculoResumo['custo_manutencao_periodo'], 2, ',', '.'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo dashboard_executive_alert_badge((string) $veiculoResumo['preventiva_status']); ?>">
                                            <?php echo htmlspecialchars(dashboard_executive_alert_label((string) $veiculoResumo['preventiva_status']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (! empty($veiculoResumo['deleted_at'])): ?>
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-200 text-slate-700">Arquivado</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-2"><?php echo (int) $veiculoResumo['total_alertas']; ?> alerta(s) consolidados</div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $veiculoResumo['preventiva_resumo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Ultimos abastecimentos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Motorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Combustivel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($abastecimentosRecentes)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum abastecimento registrado ate o momento.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($abastecimentosRecentes as $abastecimento): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $abastecimento['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $abastecimento['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars((string) $abastecimento['motorista_nome'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $abastecimento['secretaria'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars(strtoupper(str_replace('_', ' ', (string) $abastecimento['tipo_combustivel'])), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $abastecimento['data_abastecimento'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div>R$ <?php echo number_format((float) $abastecimento['valor_total'], 2, ',', '.'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo number_format((float) $abastecimento['litros'], 2, ',', '.'); ?> L</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h2 class="text-lg font-semibold text-slate-700">Ultimas manutencoes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($manutencoesRecentes)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Nenhuma manutencao registrada ate o momento.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($manutencoesRecentes as $manutencaoItem): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars((string) $manutencaoItem['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $manutencaoItem['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-800"><?php echo htmlspecialchars(ucfirst((string) $manutencaoItem['tipo']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $manutencaoItem['data_abertura'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ((string) $manutencaoItem['status']) === 'concluida' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $manutencaoItem['status'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    R$ <?php echo number_format((float) ($manutencaoItem['custo_final'] > 0 ? $manutencaoItem['custo_final'] : $manutencaoItem['custo_estimado']), 2, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-700">Frota atualizada</h2>
                        <p class="text-sm text-slate-500">Consulta <?php echo htmlspecialchars(dashboard_vehicle_filter_label($filtroFrota), ENT_QUOTES, 'UTF-8'); ?> com historico de arquivamento.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <a href="/dashboard.php?frota=ativos" class="rounded-full px-3 py-1.5 <?php echo $filtroFrota === 'ativos' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200'; ?>">Ativos</a>
                        <a href="/dashboard.php?frota=arquivados" class="rounded-full px-3 py-1.5 <?php echo $filtroFrota === 'arquivados' ? 'bg-slate-700 text-white' : 'bg-white text-slate-600 border border-slate-200'; ?>">Arquivados</a>
                        <a href="/dashboard.php?frota=todos" class="rounded-full px-3 py-1.5 <?php echo $filtroFrota === 'todos' ? 'bg-cyan-700 text-white' : 'bg-white text-slate-600 border border-slate-200'; ?>">Todos</a>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Veiculo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Lotacao</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Historico</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($veiculos)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Nenhum veiculo encontrado para o filtro selecionado.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($veiculos as $v): ?>
                            <?php $veiculoArquivado = ! empty($v['deleted_at']); ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($v['placa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($v['modelo'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <div><?php echo htmlspecialchars((string) ($v['secretaria_lotada'] ?: 'Nao informada'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500">
                                        <?php echo htmlspecialchars((string) (($v['tipo'] ?: 'Tipo n/i') . ' - ' . ($v['combustivel'] ?: 'Combustivel n/i')), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="text-xs text-slate-400">Km inicial: <?php echo (int) ($v['quilometragem_inicial'] ?? 0); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo dashboard_vehicle_status_badge((string) $v['status']); ?>">
                                        <?php echo htmlspecialchars(dashboard_vehicle_status_label((string) $v['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?php if ($veiculoArquivado): ?>
                                        <div class="font-medium text-slate-900">Arquivado</div>
                                        <div class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $v['deleted_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php else: ?>
                                        <div class="font-medium text-emerald-700">Ativo</div>
                                        <div class="text-xs text-slate-500">Disponivel para operacao</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <?php if ($canManageFleet): ?>
                                        <form method="POST" action="/veiculos.php" class="inline-flex">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="<?php echo $veiculoArquivado ? 'restore_veiculo' : 'archive_veiculo'; ?>">
                                            <input type="hidden" name="placa" value="<?php echo htmlspecialchars($v['placa'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="<?php echo $veiculoArquivado ? 'text-emerald-600 hover:text-emerald-800' : 'text-amber-600 hover:text-amber-800'; ?>" onclick="return confirm('<?php echo $veiculoArquivado ? 'Tem certeza que deseja restaurar este veiculo?' : 'Tem certeza que deseja arquivar este veiculo?'; ?>');">
                                                <?php echo $veiculoArquivado ? 'Restaurar' : 'Arquivar'; ?>
                                            </button>
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
