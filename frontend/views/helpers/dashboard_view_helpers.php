<?php

declare(strict_types=1);

/**
 * @param list<array<string, mixed>> $veiculos
 * @return array{operacao:int,manutencao:int}
 */
function dashboard_summarize_vehicle_statuses(array $veiculos): array
{
    $operacao = 0;
    $manutencao = 0;

    foreach ($veiculos as $veiculo) {
        $status = strtolower((string) ($veiculo['status'] ?? ''));

        if (in_array($status, ['ativo', 'disponivel', 'em_viagem', 'reservado'], true)) {
            $operacao++;
        }

        if (in_array($status, ['manutencao', 'em_manutencao'], true)) {
            $manutencao++;
        }
    }

    return [
        'operacao' => $operacao,
        'manutencao' => $manutencao,
    ];
}

/**
 * @param list<array<string, mixed>> $motoristas
 * @return array{ativos:int,cnhs_vencendo:int}
 */
function dashboard_summarize_motoristas(
    array $motoristas,
    DateTimeImmutable $today,
    DateTimeImmutable $alertLimit
): array {
    $ativos = 0;
    $cnhsVencendo = 0;

    foreach ($motoristas as $motorista) {
        if (($motorista['status'] ?? '') === 'ativo') {
            $ativos++;
        }

        $vencimento = DateTimeImmutable::createFromFormat('Y-m-d', (string) ($motorista['cnh_vencimento'] ?? ''));
        if ($vencimento instanceof DateTimeImmutable && $vencimento >= $today && $vencimento <= $alertLimit) {
            $cnhsVencendo++;
        }
    }

    return [
        'ativos' => $ativos,
        'cnhs_vencendo' => $cnhsVencendo,
    ];
}

/**
 * @param list<array<string, mixed>> $abastecimentos
 */
function dashboard_count_recent_refuels(array $abastecimentos, DateTimeImmutable $today): int
{
    $total = 0;
    $limite = $today->modify('-7 days');

    foreach ($abastecimentos as $abastecimento) {
        $data = DateTimeImmutable::createFromFormat('Y-m-d', (string) ($abastecimento['data_abastecimento'] ?? ''));
        if ($data instanceof DateTimeImmutable && $data >= $limite) {
            $total++;
        }
    }

    return $total;
}

/**
 * @return list<string>
 */
function dashboard_build_operational_alerts(
    int $veiculosManutencao,
    int $manutencoesAbertas,
    int $preventivasVencidas,
    int $preventivasProximas,
    int $alertasAbastecimento,
    int $cnhsVencendo,
    int $veiculosArquivados
): array {
    $alertas = [];

    if ($veiculosManutencao > 0) {
        $alertas[] = $veiculosManutencao . ' veiculo(s) estao em manutencao neste momento.';
    }
    if ($manutencoesAbertas > 0) {
        $alertas[] = $manutencoesAbertas . ' manutencao(oes) seguem abertas ou em andamento.';
    }
    if ($preventivasVencidas > 0) {
        $alertas[] = $preventivasVencidas . ' preventiva(s) estao vencidas e pedem acao imediata.';
    }
    if ($preventivasProximas > 0) {
        $alertas[] = $preventivasProximas . ' preventiva(s) entram em janela de atencao nos proximos dias ou kms.';
    }
    if ($alertasAbastecimento > 0) {
        $alertas[] = $alertasAbastecimento . ' abastecimento(s) apresentam anomalias de consumo ou custo no periodo.';
    }
    if ($cnhsVencendo > 0) {
        $alertas[] = $cnhsVencendo . ' CNH(s) vencem nos proximos 30 dias.';
    }
    if ($veiculosArquivados > 0) {
        $alertas[] = $veiculosArquivados . ' veiculo(s) seguem arquivados e disponiveis para consulta ou restauracao.';
    }

    return $alertas;
}

/**
 * @return list<array{title:string,value:string,icon_background:string,icon_svg:string}>
 */
function dashboard_build_primary_metric_cards(
    int $totalFrota,
    int $veiculosOperacao,
    int $veiculosManutencao,
    float $custoOperacionalPeriodo
): array {
    return [
        [
            'title' => 'Total da Frota',
            'value' => (string) $totalFrota,
            'icon_background' => 'bg-blue-500',
            'icon_svg' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>',
        ],
        [
            'title' => 'Em Operacao',
            'value' => (string) $veiculosOperacao,
            'icon_background' => 'bg-emerald-500',
            'icon_svg' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        ],
        [
            'title' => 'Manutencao',
            'value' => (string) $veiculosManutencao,
            'icon_background' => 'bg-amber-500',
            'icon_svg' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
        ],
        [
            'title' => 'Custo do Periodo',
            'value' => 'R$ ' . number_format($custoOperacionalPeriodo, 2, ',', '.'),
            'icon_background' => 'bg-cyan-600',
            'icon_svg' => '<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m3 0h6M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"></path></svg>',
        ],
    ];
}

/**
 * @return list<array{title:string,value:string,value_class:string,description:?string}>
 */
function dashboard_build_secondary_metric_cards(
    int $manutencoesAbertas,
    int $abastecimentosUltimos7Dias,
    int $motoristasAtivos,
    int $cnhsVencendo,
    int $preventivasVencidas,
    int $preventivasProximas,
    float $consumoMedioPeriodo,
    int $veiculosArquivados
): array {
    return [
        ['title' => 'Manutencoes abertas', 'value' => (string) $manutencoesAbertas, 'value_class' => 'text-amber-600', 'description' => null],
        ['title' => 'Abastecimentos em 7 dias', 'value' => (string) $abastecimentosUltimos7Dias, 'value_class' => 'text-cyan-700', 'description' => null],
        ['title' => 'Motoristas ativos', 'value' => (string) $motoristasAtivos, 'value_class' => 'text-emerald-600', 'description' => null],
        ['title' => 'CNHs vencendo', 'value' => (string) $cnhsVencendo, 'value_class' => 'text-rose-600', 'description' => null],
        ['title' => 'Prev. vencidas', 'value' => (string) $preventivasVencidas, 'value_class' => 'text-rose-700', 'description' => null],
        ['title' => 'Prev. proximas', 'value' => (string) $preventivasProximas, 'value_class' => 'text-amber-600', 'description' => null],
        ['title' => 'Consumo medio', 'value' => $consumoMedioPeriodo > 0 ? number_format($consumoMedioPeriodo, 2, ',', '.') : '--', 'value_class' => 'text-cyan-700', 'description' => null],
        ['title' => 'Arquivados', 'value' => (string) $veiculosArquivados, 'value_class' => 'text-slate-700', 'description' => null],
    ];
}

/**
 * @return list<array{href:string,title:string,description:string,classes:string}>
 */
function dashboard_build_quick_actions(bool $canManageUsers): array
{
    $actions = [
        [
            'href' => '/motoristas.php',
            'title' => 'Motoristas',
            'description' => 'Cadastro e situacao das CNHs',
            'classes' => 'hover:border-cyan-300 hover:bg-cyan-50',
        ],
        [
            'href' => '/manutencoes.php',
            'title' => 'Manutencoes',
            'description' => 'Abertura e acompanhamento das OS',
            'classes' => 'hover:border-amber-300 hover:bg-amber-50',
        ],
        [
            'href' => '/abastecimentos.php',
            'title' => 'Abastecimentos',
            'description' => 'Registro de combustivel e custo',
            'classes' => 'hover:border-emerald-300 hover:bg-emerald-50',
        ],
    ];

    if ($canManageUsers) {
        $actions[] = [
            'href' => '/user_management.php',
            'title' => 'Usuarios',
            'description' => 'Perfis e acessos do sistema',
            'classes' => 'hover:border-slate-400 hover:bg-slate-50',
        ];
    }

    return $actions;
}

/**
 * @param list<array<string, mixed>> $veiculosAtivos
 * @param list<array<string, mixed>> $motoristas
 * @param list<array<string, mixed>> $abastecimentosRecentes
 * @param list<array<string, mixed>> $painelSecretarias
 * @param list<array<string, mixed>> $painelVeiculos
 * @return array{
 *     alertas_operacionais:list<string>,
 *     primary_metric_cards:list<array{title:string,value:string,icon_background:string,icon_svg:string}>,
 *     secondary_metric_cards:list<array{title:string,value:string,value_class:string,description:?string}>,
 *     quick_actions:list<array{href:string,title:string,description:string,classes:string}>,
 *     executive_overview_cards:list<array{title:string,value:string,description:string}>,
 *     fleet_filter_tabs:list<array{label:string,href:string,is_active:bool}>
 * }
 */
function dashboard_build_page_data(
    array $veiculosAtivos,
    array $motoristas,
    array $abastecimentosRecentes,
    array $painelSecretarias,
    array $painelVeiculos,
    bool $canManageUsers,
    string $filtroFrota,
    DateTimeImmutable $today,
    DateTimeImmutable $alertLimit,
    int $totalFrota,
    int $manutencoesAbertas,
    int $preventivasVencidas,
    int $preventivasProximas,
    int $alertasAbastecimento,
    int $veiculosArquivados,
    float $custoOperacionalPeriodo,
    float $consumoMedioPeriodo
): array {
    $statusResumo = dashboard_summarize_vehicle_statuses($veiculosAtivos);
    $motoristasResumo = dashboard_summarize_motoristas($motoristas, $today, $alertLimit);
    $abastecimentosUltimos7Dias = dashboard_count_recent_refuels($abastecimentosRecentes, $today);

    return [
        'alertas_operacionais' => dashboard_build_operational_alerts(
            $statusResumo['manutencao'],
            $manutencoesAbertas,
            $preventivasVencidas,
            $preventivasProximas,
            $alertasAbastecimento,
            $motoristasResumo['cnhs_vencendo'],
            $veiculosArquivados
        ),
        'primary_metric_cards' => dashboard_build_primary_metric_cards(
            $totalFrota,
            $statusResumo['operacao'],
            $statusResumo['manutencao'],
            $custoOperacionalPeriodo
        ),
        'secondary_metric_cards' => dashboard_build_secondary_metric_cards(
            $manutencoesAbertas,
            $abastecimentosUltimos7Dias,
            $motoristasResumo['ativos'],
            $motoristasResumo['cnhs_vencendo'],
            $preventivasVencidas,
            $preventivasProximas,
            $consumoMedioPeriodo,
            $veiculosArquivados
        ),
        'quick_actions' => dashboard_build_quick_actions($canManageUsers),
        'executive_overview_cards' => dashboard_build_executive_overview_cards($painelSecretarias, $painelVeiculos),
        'fleet_filter_tabs' => dashboard_build_fleet_filter_tabs($filtroFrota),
    ];
}

/**
 * @param list<array<string, mixed>> $painelSecretarias
 * @param list<array<string, mixed>> $painelVeiculos
 * @return list<array{title:string,value:string,description:string}>
 */
function dashboard_build_executive_overview_cards(array $painelSecretarias, array $painelVeiculos): array
{
    $secretariasMonitoradas = count($painelSecretarias);
    $topSecretaria = $painelSecretarias[0] ?? null;
    $topVeiculoExecutivo = $painelVeiculos[0] ?? null;

    return [
        [
            'title' => 'Secretarias monitoradas',
            'value' => (string) $secretariasMonitoradas,
            'description' => 'Consolidacao de frota, viagens, custo e alertas no periodo.',
        ],
        [
            'title' => 'Secretaria com maior custo',
            'value' => is_array($topSecretaria)
                ? (string) ($topSecretaria['secretaria'] ?? '--')
                : '--',
            'description' => is_array($topSecretaria)
                ? 'R$ ' . number_format((float) ($topSecretaria['custo_total_periodo'] ?? 0), 2, ',', '.') . ' no periodo'
                : 'Sem custo consolidado ate o momento.',
        ],
        [
            'title' => 'Veiculo mais sensivel',
            'value' => is_array($topVeiculoExecutivo)
                ? (string) ($topVeiculoExecutivo['placa'] ?? '--')
                : '--',
            'description' => is_array($topVeiculoExecutivo)
                ? (int) ($topVeiculoExecutivo['total_alertas'] ?? 0) . ' alerta(s) e R$ '
                    . number_format((float) ($topVeiculoExecutivo['custo_total_periodo'] ?? 0), 2, ',', '.')
                : 'Sem consolidacao por veiculo no periodo.',
        ],
    ];
}

/**
 * @return list<array{label:string,href:string,is_active:bool}>
 */
function dashboard_build_fleet_filter_tabs(string $currentFilter): array
{
    $tabs = [];
    $labels = [
        'ativos' => 'Ativos',
        'arquivados' => 'Arquivados',
        'todos' => 'Todos',
    ];

    foreach ($labels as $filter => $label) {
        $tabs[] = [
            'label' => $label,
            'href' => '/dashboard.php?frota=' . $filter,
            'is_active' => $currentFilter === $filter,
        ];
    }

    return $tabs;
}

function dashboard_vehicle_status_label(string $status): string
{
    return match ($status) {
        'ativo' => 'Disponivel',
        'manutencao' => 'Em manutencao',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

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
