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
 * @param list<array<string, mixed>> $veiculos
 * @return array{vencidos:int,vencendo:int}
 */
function dashboard_summarize_document_expirations(
    array $veiculos,
    DateTimeImmutable $today,
    DateTimeImmutable $alertLimit
): array {
    $vencidos = 0;
    $vencendo = 0;

    foreach ($veiculos as $veiculo) {
        foreach (dashboard_collect_vehicle_document_alerts($veiculo, $today, $alertLimit) as $alerta) {
            if ($alerta['status'] === 'vencido') {
                $vencidos++;
                continue;
            }

            $vencendo++;
        }
    }

    return [
        'vencidos' => $vencidos,
        'vencendo' => $vencendo,
    ];
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
    int $checklistsNaoConformes,
    int $cnhsVencendo,
    int $veiculosArquivados,
    int $documentosVencidos,
    int $documentosVencendo
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
    if ($checklistsNaoConformes > 0) {
        $alertas[] = $checklistsNaoConformes . ' checklist(s) registraram nao conformidade no periodo recente.';
    }
    if ($cnhsVencendo > 0) {
        $alertas[] = $cnhsVencendo . ' CNH(s) vencem nos proximos 30 dias.';
    }
    if ($documentosVencidos > 0) {
        $alertas[] = $documentosVencidos . ' documento(s) veicular(es) estao vencidos.';
    }
    if ($documentosVencendo > 0) {
        $alertas[] = $documentosVencendo . ' documento(s) veicular(es) vencem nos proximos 30 dias.';
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
    int $checklistsRecentes,
    int $motoristasAtivos,
    int $cnhsVencendo,
    int $preventivasVencidas,
    int $preventivasProximas,
    float $consumoMedioPeriodo,
    int $documentosVencendo
): array {
    return [
        ['title' => 'Manutencoes abertas', 'value' => (string) $manutencoesAbertas, 'value_class' => 'text-amber-600', 'description' => null],
        ['title' => 'Abastecimentos em 7 dias', 'value' => (string) $abastecimentosUltimos7Dias, 'value_class' => 'text-cyan-700', 'description' => null],
        ['title' => 'Checklists recentes', 'value' => (string) $checklistsRecentes, 'value_class' => 'text-indigo-700', 'description' => null],
        ['title' => 'Motoristas ativos', 'value' => (string) $motoristasAtivos, 'value_class' => 'text-emerald-600', 'description' => null],
        ['title' => 'CNHs vencendo', 'value' => (string) $cnhsVencendo, 'value_class' => 'text-rose-600', 'description' => null],
        ['title' => 'Prev. vencidas', 'value' => (string) $preventivasVencidas, 'value_class' => 'text-rose-700', 'description' => null],
        ['title' => 'Prev. proximas', 'value' => (string) $preventivasProximas, 'value_class' => 'text-amber-600', 'description' => null],
        ['title' => 'Consumo medio', 'value' => $consumoMedioPeriodo > 0 ? number_format($consumoMedioPeriodo, 2, ',', '.') : '--', 'value_class' => 'text-cyan-700', 'description' => null],
        ['title' => 'Docs vencendo', 'value' => (string) $documentosVencendo, 'value_class' => 'text-rose-700', 'description' => null],
    ];
}

/**
 * @param list<array<string, mixed>> $checklists
 * @return array{recentes:int,nao_conformes:int,evidencias:int}
 */
function dashboard_summarize_checklists(array $checklists): array
{
    $naoConformes = 0;
    $evidencias = 0;

    foreach ($checklists as $checklist) {
        if (($checklist['status_conformidade'] ?? '') === 'nao_conforme') {
            $naoConformes++;
        }

        $decodedEvidence = json_decode((string) ($checklist['evidencias_json'] ?? '[]'), true);
        if (is_array($decodedEvidence)) {
            foreach ($decodedEvidence as $entry) {
                if (trim((string) ($entry['referencia'] ?? '')) !== '') {
                    $evidencias++;
                }
            }
        } elseif (trim((string) ($checklist['evidencia_referencia'] ?? '')) !== '') {
            $evidencias++;
        }
    }

    return [
        'recentes' => count($checklists),
        'nao_conformes' => $naoConformes,
        'evidencias' => $evidencias,
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
 * @param list<array<string, mixed>> $checklistsRecentes
 * @param list<array<string, mixed>> $painelSecretarias
 * @param list<array<string, mixed>> $painelVeiculos
 * @return array{
 *     alertas_operacionais:list<string>,
 *     primary_metric_cards:list<array{title:string,value:string,icon_background:string,icon_svg:string}>,
 *     secondary_metric_cards:list<array{title:string,value:string,value_class:string,description:?string}>,
 *     quick_actions:list<array{href:string,title:string,description:string,classes:string}>,
 *     executive_overview_cards:list<array{title:string,value:string,description:string}>,
 *     fleet_filter_tabs:list<array{label:string,href:string,is_active:bool}>,
 *     document_secretaria_rows:list<array{
 *         secretaria:string,
 *         total_pendencias:string,
 *         documentos_vencidos:string,
 *         documentos_vencendo:string,
 *         veiculos_afetados:string,
 *         status_class:string
 *     }>,
 *     document_pending_rows:list<array{
 *         placa:string,
 *         secretaria_lotada:string,
 *         pendencias:string,
 *         status_badge:string,
 *         status_badge_class:string
 *     }>,
 *     checklist_rows:list<array{
 *         tipo:string,
 *         placa:string,
 *         secretaria:string,
 *         status_badge:string,
 *         status_badge_class:string,
 *         resumo:string,
 *         evidencias:string
 *     }>
 * }
 */
function dashboard_build_page_data(
    array $veiculosAtivos,
    array $motoristas,
    array $abastecimentosRecentes,
    array $checklistsRecentes,
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
    $documentosResumo = dashboard_summarize_document_expirations($veiculosAtivos, $today, $alertLimit);
    $checklistsResumo = dashboard_summarize_checklists($checklistsRecentes);

    return [
        'alertas_operacionais' => dashboard_build_operational_alerts(
            $statusResumo['manutencao'],
            $manutencoesAbertas,
            $preventivasVencidas,
            $preventivasProximas,
            $alertasAbastecimento,
            $checklistsResumo['nao_conformes'],
            $motoristasResumo['cnhs_vencendo'],
            $veiculosArquivados,
            $documentosResumo['vencidos'],
            $documentosResumo['vencendo']
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
            $checklistsResumo['recentes'],
            $motoristasResumo['ativos'],
            $motoristasResumo['cnhs_vencendo'],
            $preventivasVencidas,
            $preventivasProximas,
            $consumoMedioPeriodo,
            $documentosResumo['vencendo'] + $documentosResumo['vencidos']
        ),
        'quick_actions' => dashboard_build_quick_actions($canManageUsers),
        'executive_overview_cards' => dashboard_build_executive_overview_cards($painelSecretarias, $painelVeiculos),
        'fleet_filter_tabs' => dashboard_build_fleet_filter_tabs($filtroFrota),
        'secretaria_rows' => dashboard_build_secretaria_rows($painelSecretarias),
        'executive_vehicle_rows' => dashboard_build_executive_vehicle_rows($painelVeiculos),
        'recent_refuel_rows' => dashboard_build_recent_refuel_rows($abastecimentosRecentes),
        'document_secretaria_rows' => dashboard_build_document_secretaria_rows($veiculosAtivos, $today, $alertLimit),
        'document_pending_rows' => dashboard_build_document_pending_rows($veiculosAtivos, $today, $alertLimit),
        'checklist_rows' => dashboard_build_checklist_rows($checklistsRecentes),
    ];
}

/**
 * @param list<array<string, mixed>> $checklists
 * @return list<array{
 *     tipo:string,
 *     placa:string,
 *     secretaria:string,
 *     status_badge:string,
 *     status_badge_class:string,
 *     resumo:string,
 *     evidencias:string
 * }>
 */
function dashboard_build_checklist_rows(array $checklists): array
{
    $rows = [];

    foreach (array_slice($checklists, 0, 5) as $checklist) {
        $status = (string) ($checklist['status_conformidade'] ?? 'pendente');
        $evidenceCount = 0;
        $decodedEvidence = json_decode((string) ($checklist['evidencias_json'] ?? '[]'), true);

        if (is_array($decodedEvidence)) {
            foreach ($decodedEvidence as $entry) {
                if (trim((string) ($entry['referencia'] ?? '')) !== '') {
                    $evidenceCount++;
                }
            }
        } elseif (trim((string) ($checklist['evidencia_referencia'] ?? '')) !== '') {
            $evidenceCount = 1;
        }

        $rows[] = [
            'tipo' => ucfirst((string) ($checklist['tipo'] ?? '')),
            'placa' => (string) ($checklist['placa'] ?? ''),
            'secretaria' => (string) ($checklist['secretaria'] ?? 'Nao informada'),
            'status_badge' => ucfirst(str_replace('_', ' ', $status)),
            'status_badge_class' => match ($status) {
                'conforme' => 'bg-emerald-100 text-emerald-800',
                'nao_conforme' => 'bg-rose-100 text-rose-800',
                default => 'bg-amber-100 text-amber-800',
            },
            'resumo' => (string) (($checklist['nao_conformidades'] ?? '') !== '' ? $checklist['nao_conformidades'] : 'Sem nao conformidade registrada.'),
            'evidencias' => $evidenceCount . ' evidencia(s)',
        ];
    }

    return $rows;
}

/**
 * @param list<array<string, mixed>> $veiculos
 * @return list<array{
 *     placa:string,
 *     secretaria_lotada:string,
 *     pendencias:string,
 *     status_badge:string,
 *     status_badge_class:string
 * }>
 */
function dashboard_build_document_pending_rows(
    array $veiculos,
    DateTimeImmutable $today,
    DateTimeImmutable $alertLimit
): array {
    $rows = [];

    foreach ($veiculos as $veiculo) {
        $alertas = dashboard_collect_vehicle_document_alerts($veiculo, $today, $alertLimit);

        if ($alertas === []) {
            continue;
        }

        $hasExpired = count(array_filter(
            $alertas,
            static fn (array $alerta): bool => ($alerta['status'] ?? '') === 'vencido'
        )) > 0;

        $rows[] = [
            'placa' => (string) ($veiculo['placa'] ?? ''),
            'secretaria_lotada' => (string) ($veiculo['secretaria_lotada'] ?? 'Nao informada'),
            'pendencias' => implode(' | ', array_map(
                static fn (array $alerta): string => (string) ($alerta['label'] ?? ''),
                $alertas
            )),
            'status_badge' => $hasExpired ? 'Vencido' : 'Vencendo',
            'status_badge_class' => $hasExpired ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800',
        ];
    }

    usort($rows, static function (array $left, array $right): int {
        $leftRank = $left['status_badge'] === 'Vencido' ? 0 : 1;
        $rightRank = $right['status_badge'] === 'Vencido' ? 0 : 1;

        return [$leftRank, $left['placa']] <=> [$rightRank, $right['placa']];
    });

    return array_slice($rows, 0, 6);
}

/**
 * @param list<array<string, mixed>> $veiculos
 * @return list<array{
 *     secretaria:string,
 *     total_pendencias:string,
 *     documentos_vencidos:string,
 *     documentos_vencendo:string,
 *     veiculos_afetados:string,
 *     status_class:string
 * }>
 */
function dashboard_build_document_secretaria_rows(
    array $veiculos,
    DateTimeImmutable $today,
    DateTimeImmutable $alertLimit
): array {
    $grouped = [];

    foreach ($veiculos as $veiculo) {
        $alertas = dashboard_collect_vehicle_document_alerts($veiculo, $today, $alertLimit);

        if ($alertas === []) {
            continue;
        }

        $secretaria = trim((string) ($veiculo['secretaria_lotada'] ?? ''));
        $secretaria = $secretaria !== '' ? $secretaria : 'Secretaria nao informada';

        $grouped[$secretaria] ??= [
            'secretaria' => $secretaria,
            'documentos_vencidos' => 0,
            'documentos_vencendo' => 0,
            'veiculos_afetados' => [],
        ];

        foreach ($alertas as $alerta) {
            if (($alerta['status'] ?? '') === 'vencido') {
                $grouped[$secretaria]['documentos_vencidos']++;
            } else {
                $grouped[$secretaria]['documentos_vencendo']++;
            }
        }

        $placa = (string) ($veiculo['placa'] ?? '');
        if ($placa !== '') {
            $grouped[$secretaria]['veiculos_afetados'][$placa] = true;
        }
    }

    $rows = [];

    foreach ($grouped as $item) {
        $vencidos = (int) $item['documentos_vencidos'];
        $vencendo = (int) $item['documentos_vencendo'];
        $veiculosAfetados = count($item['veiculos_afetados']);
        $totalPendencias = $vencidos + $vencendo;

        $rows[] = [
            'secretaria' => (string) $item['secretaria'],
            'total_pendencias' => (string) $totalPendencias . ' pendencia(s)',
            'documentos_vencidos' => (string) $vencidos . ' vencido(s)',
            'documentos_vencendo' => (string) $vencendo . ' vencendo',
            'veiculos_afetados' => (string) $veiculosAfetados . ' veiculo(s)',
            'status_class' => $vencidos > 0 ? 'text-rose-700' : 'text-amber-700',
        ];
    }

    usort($rows, static function (array $left, array $right): int {
        preg_match('/^(\d+)/', $left['total_pendencias'], $leftMatch);
        preg_match('/^(\d+)/', $right['total_pendencias'], $rightMatch);
        $leftTotal = (int) ($leftMatch[1] ?? 0);
        $rightTotal = (int) ($rightMatch[1] ?? 0);

        return [$rightTotal, $left['secretaria']] <=> [$leftTotal, $right['secretaria']];
    });

    return array_slice($rows, 0, 6);
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

/**
 * @param list<array<string, mixed>> $painelSecretarias
 * @return list<array{
 *     secretaria:string,
 *     frota_resumo:string,
 *     custo_total:string,
 *     viagens_km:string,
 *     disponibilidade:string,
 *     abastecimentos:string,
 *     alertas:string,
 *     alertas_class:string
 * }>
 */
function dashboard_build_secretaria_rows(array $painelSecretarias): array
{
    $rows = [];

    foreach (array_slice($painelSecretarias, 0, 6) as $secretariaResumo) {
        $alertasTotal = (int) ($secretariaResumo['alertas_total'] ?? 0);

        $rows[] = [
            'secretaria' => (string) ($secretariaResumo['secretaria'] ?? ''),
            'frota_resumo' => sprintf(
                'Frota ativa: %d | Em operacao: %d | Motoristas ativos: %d',
                (int) ($secretariaResumo['frota_ativa'] ?? 0),
                (int) ($secretariaResumo['frota_operacao'] ?? 0),
                (int) ($secretariaResumo['motoristas_ativos'] ?? 0)
            ),
            'custo_total' => 'R$ ' . number_format((float) ($secretariaResumo['custo_total_periodo'] ?? 0), 2, ',', '.'),
            'viagens_km' => sprintf(
                '%d / %s',
                (int) ($secretariaResumo['viagens_periodo'] ?? 0),
                number_format((float) ($secretariaResumo['km_viagens_periodo'] ?? 0), 0, ',', '.')
            ),
            'disponibilidade' => ($secretariaResumo['disponibilidade_percentual'] ?? null) !== null
                ? number_format((float) $secretariaResumo['disponibilidade_percentual'], 1, ',', '.') . '%'
                : '--',
            'abastecimentos' => (int) ($secretariaResumo['abastecimentos_periodo'] ?? 0) . ' registro(s)',
            'alertas' => $alertasTotal . ' alerta(s)',
            'alertas_class' => $alertasTotal > 0 ? 'text-amber-700' : 'text-emerald-700',
        ];
    }

    return $rows;
}

/**
 * @param list<array<string, mixed>> $painelVeiculos
 * @return list<array{
 *     placa:string,
 *     modelo:string,
 *     secretaria_lotada:string,
 *     uso_viagens:string,
 *     uso_km:string,
 *     uso_abastecimentos:string,
 *     custo_total:string,
 *     custo_abastecimento:string,
 *     custo_manutencao:string,
 *     preventiva_badge_class:string,
 *     preventiva_badge_label:string,
 *     exibir_arquivado:bool,
 *     total_alertas:string,
 *     preventiva_resumo:string
 * }>
 */
function dashboard_build_executive_vehicle_rows(array $painelVeiculos): array
{
    $rows = [];

    foreach ($painelVeiculos as $veiculoResumo) {
        $preventivaStatus = (string) ($veiculoResumo['preventiva_status'] ?? '');

        $rows[] = [
            'placa' => (string) ($veiculoResumo['placa'] ?? ''),
            'modelo' => (string) ($veiculoResumo['modelo'] ?? ''),
            'secretaria_lotada' => (string) ($veiculoResumo['secretaria_lotada'] ?? ''),
            'uso_viagens' => (int) ($veiculoResumo['viagens_periodo'] ?? 0) . ' viagem(ns)',
            'uso_km' => number_format((float) ($veiculoResumo['km_viagens_periodo'] ?? 0), 0, ',', '.') . ' km',
            'uso_abastecimentos' => (int) ($veiculoResumo['abastecimentos_periodo'] ?? 0) . ' abastecimento(s)',
            'custo_total' => 'R$ ' . number_format((float) ($veiculoResumo['custo_total_periodo'] ?? 0), 2, ',', '.'),
            'custo_abastecimento' => 'Abast.: R$ ' . number_format((float) ($veiculoResumo['gasto_abastecimento_periodo'] ?? 0), 2, ',', '.'),
            'custo_manutencao' => 'Manut.: R$ ' . number_format((float) ($veiculoResumo['custo_manutencao_periodo'] ?? 0), 2, ',', '.'),
            'preventiva_badge_class' => dashboard_executive_alert_badge($preventivaStatus),
            'preventiva_badge_label' => dashboard_executive_alert_label($preventivaStatus),
            'exibir_arquivado' => ! empty($veiculoResumo['deleted_at']),
            'total_alertas' => (int) ($veiculoResumo['total_alertas'] ?? 0) . ' alerta(s) consolidados',
            'preventiva_resumo' => (string) ($veiculoResumo['preventiva_resumo'] ?? ''),
        ];
    }

    return $rows;
}

/**
 * @param list<array<string, mixed>> $abastecimentosRecentes
 * @return list<array{
 *     placa:string,
 *     modelo:string,
 *     motorista_nome:string,
 *     secretaria:string,
 *     combustivel:string,
 *     data_abastecimento:string,
 *     valor_total:string,
 *     litros:string
 * }>
 */
function dashboard_build_recent_refuel_rows(array $abastecimentosRecentes): array
{
    $rows = [];

    foreach ($abastecimentosRecentes as $abastecimento) {
        $rows[] = [
            'placa' => (string) ($abastecimento['placa'] ?? ''),
            'modelo' => (string) ($abastecimento['modelo'] ?? ''),
            'motorista_nome' => (string) ($abastecimento['motorista_nome'] ?? ''),
            'secretaria' => (string) ($abastecimento['secretaria'] ?? ''),
            'combustivel' => strtoupper(str_replace('_', ' ', (string) ($abastecimento['tipo_combustivel'] ?? ''))),
            'data_abastecimento' => (string) ($abastecimento['data_abastecimento'] ?? ''),
            'valor_total' => 'R$ ' . number_format((float) ($abastecimento['valor_total'] ?? 0), 2, ',', '.'),
            'litros' => number_format((float) ($abastecimento['litros'] ?? 0), 2, ',', '.') . ' L',
        ];
    }

    return $rows;
}

/**
 * @param array<string, mixed> $veiculo
 * @return list<array{status:string,label:string}>
 */
function dashboard_collect_vehicle_document_alerts(
    array $veiculo,
    DateTimeImmutable $today,
    DateTimeImmutable $alertLimit
): array {
    $documentos = [
        'licenciamento_vencimento' => 'Licenciamento',
        'seguro_vencimento' => 'Seguro',
        'crlv_vencimento' => 'CRLV',
        'contrato_vencimento' => 'Contrato',
    ];
    $alertas = [];

    foreach ($documentos as $field => $label) {
        $rawDate = (string) ($veiculo[$field] ?? '');
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $rawDate);

        if (! $date instanceof DateTimeImmutable || $date->format('Y-m-d') !== $rawDate) {
            continue;
        }

        if ($date < $today) {
            $alertas[] = [
                'status' => 'vencido',
                'label' => $label . ' vencido em ' . $rawDate,
            ];
            continue;
        }

        if ($date <= $alertLimit) {
            $alertas[] = [
                'status' => 'vencendo',
                'label' => $label . ' vence em ' . $rawDate,
            ];
        }
    }

    return $alertas;
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
