<?php

declare(strict_types=1);

/**
 * @return array<string, string>
 */
function relatorios_report_labels(): array
{
    return [
        'abastecimentos' => 'Abastecimentos',
        'manutencoes' => 'Manutencoes',
        'viagens' => 'Viagens',
        'disponibilidade' => 'Disponibilidade',
        'documentacao' => 'Documentacao',
        'transparencia' => 'Transparencia',
        'checklists' => 'Checklists',
        'auditoria' => 'Auditoria',
    ];
}

/**
 * @return array<string, string>
 */
function relatorios_status_options(string $report): array
{
    return match ($report) {
        'abastecimentos' => ['normal' => 'Normal', 'atencao' => 'Atencao', 'critico' => 'Critico'],
        'manutencoes' => ['aberta' => 'Aberta', 'em_andamento' => 'Em andamento', 'concluida' => 'Concluida', 'cancelada' => 'Cancelada'],
        'viagens' => ['em_curso' => 'Em curso', 'concluida' => 'Concluida', 'cancelada' => 'Cancelada'],
        'disponibilidade' => ['ativo' => 'Ativo', 'manutencao' => 'Manutencao', 'em_viagem' => 'Em viagem', 'reservado' => 'Reservado', 'baixado' => 'Baixado'],
        'documentacao' => ['vencido' => 'Vencido', 'vencendo' => 'Vencendo', 'regular' => 'Regular'],
        'transparencia' => ['ativo' => 'Ativo', 'manutencao' => 'Manutencao', 'em_viagem' => 'Em viagem', 'reservado' => 'Reservado', 'baixado' => 'Baixado'],
        'checklists' => ['conforme' => 'Conforme', 'nao_conforme' => 'Nao conforme', 'pendente' => 'Pendente'],
        'auditoria' => ['create' => 'Criacao', 'update' => 'Atualizacao', 'archive' => 'Arquivamento', 'restore' => 'Restauracao', 'delete' => 'Exclusao', 'blocked' => 'Bloqueio', 'export' => 'Exportacao', 'login' => 'Login', 'login_failed' => 'Falha login', 'logout' => 'Logout'],
        default => [],
    };
}

/**
 * @param array<string, mixed> $summary
 * @param array<string, mixed> $auditSummary
 * @return list<array{title:string,value:string,value_class:string}>
 */
function relatorios_summary_cards(string $report, array $summary, array $auditSummary): array
{
    if ($report === 'auditoria') {
        return [
            ['title' => 'Eventos auditados', 'value' => (string) (int) ($auditSummary['eventos_total'] ?? 0), 'value_class' => 'text-slate-800'],
            ['title' => 'Atores unicos', 'value' => (string) (int) ($auditSummary['atores_unicos'] ?? 0), 'value_class' => 'text-cyan-700'],
            ['title' => 'Exportacoes', 'value' => (string) (int) ($auditSummary['exportacoes'] ?? 0), 'value_class' => 'text-emerald-600'],
            ['title' => 'Bloqueios', 'value' => (string) (int) ($auditSummary['bloqueios'] ?? 0), 'value_class' => 'text-amber-600'],
        ];
    }

    if ($report === 'documentacao') {
        $veiculosMonitorados = (int) ($summary['veiculos_monitorados'] ?? 0);
        $veiculosPendentes = (int) ($summary['veiculos_pendentes'] ?? 0);
        $documentosVencidos = (int) ($summary['documentos_vencidos'] ?? 0);
        $documentosVencendo = (int) ($summary['documentos_vencendo'] ?? 0);

        return [
            ['title' => 'Veiculos monitorados', 'value' => (string) $veiculosMonitorados, 'value_class' => 'text-slate-800'],
            ['title' => 'Com pendencias', 'value' => (string) $veiculosPendentes, 'value_class' => 'text-amber-700'],
            ['title' => 'Documentos vencidos', 'value' => (string) $documentosVencidos, 'value_class' => 'text-rose-700'],
            ['title' => 'Documentos vencendo', 'value' => (string) $documentosVencendo, 'value_class' => 'text-cyan-700'],
        ];
    }

    if ($report === 'transparencia') {
        return [
            ['title' => 'Frota publicada', 'value' => (string) (int) ($summary['frota_publicada'] ?? 0), 'value_class' => 'text-slate-800'],
            ['title' => 'Custo consolidado', 'value' => 'R$ ' . number_format((float) ($summary['custo_total_publicado'] ?? 0), 2, ',', '.'), 'value_class' => 'text-cyan-700'],
            ['title' => 'Viagens publicadas', 'value' => (string) (int) ($summary['viagens_publicadas'] ?? 0), 'value_class' => 'text-emerald-700'],
            ['title' => 'Pendencias documentais', 'value' => (string) (int) ($summary['veiculos_com_pendencia'] ?? 0), 'value_class' => 'text-amber-700'],
        ];
    }

    if ($report === 'checklists') {
        return [
            ['title' => 'Checklists no periodo', 'value' => (string) (int) ($summary['checklists_total'] ?? 0), 'value_class' => 'text-slate-800'],
            ['title' => 'Nao conformes', 'value' => (string) (int) ($summary['nao_conformes'] ?? 0), 'value_class' => 'text-rose-700'],
            ['title' => 'Evidencias', 'value' => (string) (int) ($summary['evidencias_total'] ?? 0), 'value_class' => 'text-cyan-700'],
            ['title' => 'Itens marcados', 'value' => (string) (int) ($summary['itens_marcados'] ?? 0), 'value_class' => 'text-emerald-700'],
        ];
    }

    return [
        ['title' => 'Gasto abastecimento', 'value' => 'R$ ' . number_format((float) ($summary['gasto_abastecimento'] ?? 0), 2, ',', '.'), 'value_class' => 'text-emerald-600'],
        ['title' => 'Custo manutencao', 'value' => 'R$ ' . number_format((float) ($summary['custo_manutencao'] ?? 0), 2, ',', '.'), 'value_class' => 'text-amber-600'],
        ['title' => 'Viagens / KM', 'value' => (int) ($summary['viagens'] ?? 0) . ' / ' . number_format((float) ($summary['km_viagens'] ?? 0), 0, ',', '.'), 'value_class' => 'text-cyan-700'],
        ['title' => 'Veiculos disponiveis', 'value' => (string) (int) ($summary['veiculos_disponiveis'] ?? 0), 'value_class' => 'text-slate-800'],
    ];
}

/**
 * @return list<string>
 */
function relatorios_table_headers(string $report): array
{
    return match ($report) {
        'abastecimentos' => ['Veiculo', 'Secretaria', 'Data e combustivel', 'Consumo', 'Custos'],
        'manutencoes' => ['Veiculo', 'Secretaria', 'Tipo e periodo', 'Parceiro', 'Custos'],
        'viagens' => ['Veiculo', 'Secretaria', 'Motorista', 'Trajeto', 'KM e status'],
        'documentacao' => ['Veiculo', 'Secretaria', 'Situacao documental', 'Proximo vencimento', 'Pendencias e controle'],
        'transparencia' => ['Veiculo', 'Secretaria', 'Cadastro publico', 'Uso no periodo', 'Custos e conformidade'],
        'checklists' => ['Checklist', 'Veiculo', 'Motorista', 'Conformidade', 'Evidencias e itens'],
        'auditoria' => ['Data e evento', 'Acao', 'Alvo', 'Ator e origem', 'Contexto'],
        default => ['Veiculo', 'Secretaria', 'Status', 'Uso', 'Historico'],
    };
}

/**
 * @param array<string, string> $filters
 * @param list<string> $secretarias
 * @param list<array<string, mixed>> $veiculos
 * @param array<string, string> $statusOptions
 * @param list<string> $auditTargetTypes
 */
function relatorios_filter_fields_markup(
    string $report,
    array $filters,
    array $secretarias,
    array $veiculos,
    array $statusOptions,
    array $auditTargetTypes
): string {
    if ($report === 'auditoria') {
        return relatorios_auditoria_filter_fields_markup($filters, $statusOptions, $auditTargetTypes);
    }

    return relatorios_operational_filter_fields_markup($filters, $secretarias, $veiculos, $statusOptions);
}

/**
 * @param array<string, string> $filters
 * @param array<string, string> $statusOptions
 * @param list<string> $auditTargetTypes
 */
function relatorios_auditoria_filter_fields_markup(array $filters, array $statusOptions, array $auditTargetTypes): string
{
    $targetOptions = '<option value="">Todos os modulos</option>';

    foreach ($auditTargetTypes as $targetType) {
        $selected = $filters['tipo_alvo'] === $targetType ? 'selected' : '';
        $targetOptions .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($targetType, ENT_QUOTES, 'UTF-8'),
            $selected,
            htmlspecialchars(ucfirst($targetType), ENT_QUOTES, 'UTF-8')
        );
    }

    return sprintf(
        '<input type="text" name="ator" value="%s" placeholder="Ator ou usuario" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
        <input type="text" name="evento" value="%s" placeholder="Evento, ex: relatorio.exported" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">
        <select name="tipo_alvo" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">%s</select>
        <select name="status" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">%s</select>',
        htmlspecialchars($filters['ator'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($filters['evento'], ENT_QUOTES, 'UTF-8'),
        $targetOptions,
        relatorios_options_markup($statusOptions, $filters['status'], 'Todas as acoes')
    );
}

/**
 * @param array<string, string> $filters
 * @param list<string> $secretarias
 * @param list<array<string, mixed>> $veiculos
 * @param array<string, string> $statusOptions
 */
function relatorios_operational_filter_fields_markup(
    array $filters,
    array $secretarias,
    array $veiculos,
    array $statusOptions
): string {
    $secretariaOptions = '<option value="">Todas as secretarias</option>';
    foreach ($secretarias as $secretaria) {
        $selected = $filters['secretaria'] === $secretaria ? 'selected' : '';
        $secretariaOptions .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($secretaria, ENT_QUOTES, 'UTF-8'),
            $selected,
            htmlspecialchars($secretaria, ENT_QUOTES, 'UTF-8')
        );
    }

    $veiculoOptions = '<option value="">Todos os veiculos</option>';
    foreach ($veiculos as $veiculo) {
        $selected = $filters['veiculo_id'] === (string) $veiculo['id'] ? 'selected' : '';
        $label = (string) $veiculo['placa'] . ' - ' . (string) $veiculo['modelo'];
        $veiculoOptions .= sprintf(
            '<option value="%d" %s>%s</option>',
            (int) $veiculo['id'],
            $selected,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    return sprintf(
        '<select name="secretaria" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">%s</select>
        <select name="veiculo_id" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">%s</select>
        <select name="status" class="border border-slate-300 rounded-xl p-3 outline-none focus:ring-blue-500 focus:border-blue-500">%s</select>',
        $secretariaOptions,
        $veiculoOptions,
        relatorios_options_markup($statusOptions, $filters['status'], 'Todos os status')
    );
}

/**
 * @param array<string, string> $options
 */
function relatorios_options_markup(array $options, string $selectedValue, string $defaultLabel): string
{
    $markup = sprintf('<option value="">%s</option>', htmlspecialchars($defaultLabel, ENT_QUOTES, 'UTF-8'));

    foreach ($options as $value => $label) {
        $selected = $selectedValue === $value ? 'selected' : '';
        $markup .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            $selected,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    return $markup;
}

/**
 * @param array<string, string> $filters
 */
function relatorios_export_query(array $filters, string $report): string
{
    return http_build_query(array_merge($filters, ['relatorio' => $report, 'export' => 'csv']));
}

/**
 * @param array<string, string> $filters
 * @param array<string, string> $reportLabels
 * @return array{
 *     secretarias:list<string>,
 *     veiculos:list<array<string, mixed>>,
 *     summary:array<string, mixed>,
 *     auditSummary:array<string, mixed>,
 *     auditTargetTypes:list<string>,
 *     rows:list<array<string, mixed>>,
 *     rowMarkupList:list<string>,
 *     statusOptions:array<string, string>,
 *     summaryCards:list<array{title:string,value:string,value_class:string}>,
 *     tableHeaders:list<string>,
 *     filterFieldsMarkup:string,
 *     tabs:list<array{label:string,href:string,is_active:bool}>,
 *     exportQuery:string,
 *     reportTitle:string,
 *     clearHref:string
 * }
 */
function relatorios_build_page_data($model, string $report, array $filters, array $reportLabels): array
{
    $secretarias = $model->getSecretarias();
    $veiculos = $model->getVeiculos();
    $rows = relatorios_rows_for_report($model, $report, $filters);
    $summary = $report === 'documentacao'
        ? relatorios_documentacao_summary($rows)
        : ($report === 'transparencia'
            ? relatorios_transparencia_summary($rows)
            : ($report === 'checklists'
                ? relatorios_checklists_summary($rows)
                : $model->getResumo($filters)
            )
        );
    $auditSummary = $model->getAuditSummary($filters);
    $auditTargetTypes = $model->getAuditTargetTypes();
    $statusOptions = relatorios_status_options($report);

    return [
        'secretarias' => $secretarias,
        'veiculos' => $veiculos,
        'summary' => $summary,
        'auditSummary' => $auditSummary,
        'auditTargetTypes' => $auditTargetTypes,
        'rows' => $rows,
        'rowMarkupList' => relatorios_row_markup_list($report, $rows),
        'statusOptions' => $statusOptions,
        'summaryCards' => relatorios_summary_cards($report, $summary, $auditSummary),
        'tableHeaders' => relatorios_table_headers($report),
        'filterFieldsMarkup' => relatorios_filter_fields_markup(
            $report,
            $filters,
            $secretarias,
            $veiculos,
            $statusOptions,
            $auditTargetTypes
        ),
        'tabs' => relatorios_tabs($report, $filters, $reportLabels),
        'exportQuery' => relatorios_export_query($filters, $report),
        'reportTitle' => (string) ($reportLabels[$report] ?? 'Relatorio'),
        'clearHref' => '/relatorios.php?relatorio=' . rawurlencode($report),
    ];
}

/**
 * @param array<string, string> $filters
 * @return list<array<string, mixed>>
 */
function relatorios_rows_for_report($model, string $report, array $filters): array
{
    return match ($report) {
        'manutencoes' => $model->getManutencaoReport($filters),
        'viagens' => $model->getViagemReport($filters),
        'disponibilidade' => $model->getDisponibilidadeReport($filters),
        'documentacao' => $model->getDocumentacaoReport($filters),
        'transparencia' => $model->getTransparenciaReport($filters),
        'checklists' => $model->getChecklistReport($filters),
        'auditoria' => $model->getAuditReport($filters),
        default => $model->getAbastecimentoReport($filters),
    };
}

/**
 * @param array<string, string> $filters
 * @param array<string, string> $reportLabels
 * @return list<array{label:string,href:string,is_active:bool}>
 */
function relatorios_tabs(string $currentReport, array $filters, array $reportLabels): array
{
    $tabs = [];

    foreach ($reportLabels as $reportKey => $reportLabel) {
        $tabs[] = [
            'label' => $reportLabel,
            'href' => '/relatorios.php?' . http_build_query(array_merge($filters, ['relatorio' => $reportKey])),
            'is_active' => $currentReport === $reportKey,
        ];
    }

    return $tabs;
}

function relatorios_row_markup(string $report, array $row): string
{
    return match ($report) {
        'abastecimentos' => relatorios_abastecimento_row($row),
        'manutencoes' => relatorios_manutencao_row($row),
        'viagens' => relatorios_viagem_row($row),
        'documentacao' => relatorios_documentacao_row($row),
        'transparencia' => relatorios_transparencia_row($row),
        'checklists' => relatorios_checklist_row($row),
        'auditoria' => relatorios_auditoria_row($row),
        default => relatorios_disponibilidade_row($row),
    };
}

/**
 * @param list<array<string, mixed>> $rows
 * @return list<string>
 */
function relatorios_row_markup_list(string $report, array $rows): array
{
    $markupList = [];

    foreach ($rows as $row) {
        $markupList[] = relatorios_row_markup($report, $row);
    }

    return $markupList;
}

function relatorios_abastecimento_row(array $row): string
{
    $consumo = ($row['consumo_km_l'] ?? null) !== null ? number_format((float) $row['consumo_km_l'], 2, ',', '.') . ' km/L' : '--';
    $combustivel = strtoupper(str_replace('_', ' ', (string) $row['tipo_combustivel']));

    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>R$ %s</div><div class="text-xs text-slate-500">%s L</div></td>',
        htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['secretaria'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['data_abastecimento'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($combustivel, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($consumo, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['anomalia_status'] ?? 'normal'), ENT_QUOTES, 'UTF-8'),
        number_format((float) $row['valor_total'], 2, ',', '.'),
        number_format((float) $row['litros'], 2, ',', '.')
    );
}

function relatorios_manutencao_row(array $row): string
{
    $parceiro = (string) ($row['parceiro_nome'] ?? $row['fornecedor'] ?? 'Nao informado');
    $custo = (float) ((($row['custo_final'] ?? 0) > 0) ? $row['custo_final'] : $row['custo_estimado']);

    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>R$ %s</div></td>',
        htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['secretaria_lotada'] ?? 'Nao informada'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(ucfirst((string) $row['tipo']), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['data_abertura'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($parceiro, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8'),
        number_format($custo, 2, ',', '.')
    );
}

function relatorios_viagem_row(array $row): string
{
    $km = ($row['km_percorrido'] ?? null) !== null ? number_format((float) $row['km_percorrido'], 0, ',', '.') . ' km' : '--';

    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s &rarr; %s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>',
        htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['secretaria'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['motorista_nome'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['origem'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['destino'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['finalidade'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($km, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8')
    );
}

function relatorios_documentacao_row(array $row): string
{
    $situacao = (string) ($row['situacao_documental'] ?? 'regular');
    $badgeClass = match ($situacao) {
        'vencido' => 'bg-rose-100 text-rose-800',
        'vencendo' => 'bg-amber-100 text-amber-800',
        default => 'bg-emerald-100 text-emerald-800',
    };
    $badgeLabel = match ($situacao) {
        'vencido' => 'Vencido',
        'vencendo' => 'Vencendo',
        default => 'Regular',
    };

    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold %s">%s</span><div class="text-xs text-slate-500 mt-2">%d vencido(s) | %d vencendo</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500 mt-2">%s</div></td>',
        htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['secretaria_lotada'] ?? 'Nao informada'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'),
        (int) ($row['documentos_vencidos'] ?? 0),
        (int) ($row['documentos_vencendo'] ?? 0),
        htmlspecialchars((string) ($row['proximo_vencimento'] ?? '--'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['documentos_monitorados'] ?? 'Sem documentos monitorados.'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['pendencias_resumo'] ?? 'Nenhuma pendencia na janela atual.'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) (($row['documentos_observacoes'] ?? '') !== '' ? $row['documentos_observacoes'] : 'Sem observacoes documentais.'), ENT_QUOTES, 'UTF-8')
    );
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array<string, int>
 */
function relatorios_documentacao_summary(array $rows): array
{
    $summary = [
        'veiculos_monitorados' => count($rows),
        'veiculos_pendentes' => 0,
        'documentos_vencidos' => 0,
        'documentos_vencendo' => 0,
    ];

    foreach ($rows as $row) {
        $vencidos = (int) ($row['documentos_vencidos'] ?? 0);
        $vencendo = (int) ($row['documentos_vencendo'] ?? 0);

        if ($vencidos > 0 || $vencendo > 0) {
            $summary['veiculos_pendentes']++;
        }

        $summary['documentos_vencidos'] += $vencidos;
        $summary['documentos_vencendo'] += $vencendo;
    }

    return $summary;
}

function relatorios_transparencia_row(array $row): string
{
    $situacao = (string) ($row['situacao_publicacao'] ?? 'regular');
    $badgeClass = match ($situacao) {
        'pendencia_documental' => 'bg-amber-100 text-amber-800',
        'restricao_operacional' => 'bg-rose-100 text-rose-800',
        default => 'bg-emerald-100 text-emerald-800',
    };
    $badgeLabel = match ($situacao) {
        'pendencia_documental' => 'Pendencia documental',
        'restricao_operacional' => 'Restricao operacional',
        default => 'Regular para publicacao',
    };

    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s | %s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold %s">%s</span><div class="text-xs text-slate-500 mt-2">Status: %s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%d viagem(ns) | %s km</div><div class="text-xs text-slate-500">%d abastecimento(s) | %d manutencao(oes)</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>Total: R$ %s</div><div class="text-xs text-slate-500">Docs pendentes: %d</div></td>',
        htmlspecialchars((string) ($row['placa'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(strtoupper((string) ($row['tipo'] ?? '')), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['secretaria_lotada'] ?? 'Nao informada'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8'),
        (int) ($row['viagens_periodo'] ?? 0),
        number_format((float) ($row['km_viagens_periodo'] ?? 0), 0, ',', '.'),
        (int) ($row['abastecimentos_periodo'] ?? 0),
        (int) ($row['manutencoes_periodo'] ?? 0),
        number_format((float) (($row['gasto_abastecimento_periodo'] ?? 0) + ($row['custo_manutencao_periodo'] ?? 0)), 2, ',', '.'),
        (int) ($row['documentos_pendentes'] ?? 0)
    );
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array<string, int|float>
 */
function relatorios_transparencia_summary(array $rows): array
{
    $summary = [
        'frota_publicada' => count($rows),
        'custo_total_publicado' => 0.0,
        'viagens_publicadas' => 0,
        'veiculos_com_pendencia' => 0,
    ];

    foreach ($rows as $row) {
        $summary['custo_total_publicado'] += (float) ($row['gasto_abastecimento_periodo'] ?? 0) + (float) ($row['custo_manutencao_periodo'] ?? 0);
        $summary['viagens_publicadas'] += (int) ($row['viagens_periodo'] ?? 0);

        if ((int) ($row['documentos_pendentes'] ?? 0) > 0) {
            $summary['veiculos_com_pendencia']++;
        }
    }

    return $summary;
}

function relatorios_checklist_row(array $row): string
{
    $status = (string) ($row['status_conformidade'] ?? 'pendente');
    $badgeClass = match ($status) {
        'conforme' => 'bg-emerald-100 text-emerald-800',
        'nao_conforme' => 'bg-rose-100 text-rose-800',
        default => 'bg-amber-100 text-amber-800',
    };

    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold %s">%s</span><div class="text-xs text-slate-500 mt-2">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%d item(ns) | %d evidencia(s)</div></td>',
        htmlspecialchars(ucfirst((string) ($row['tipo'] ?? '')), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['realizado_em'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['placa'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['secretaria'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['motorista_nome'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) (($row['viagem_destino'] ?? '') !== '' ? $row['viagem_destino'] : 'Sem viagem vinculada'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(ucfirst(str_replace('_', ' ', $status)), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) (($row['nao_conformidades'] ?? '') !== '' ? $row['nao_conformidades'] : 'Sem nao conformidade registrada.'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) (($row['evidencia_referencia'] ?? '') !== '' ? $row['evidencia_referencia'] : 'Sem evidencia resumida.'), ENT_QUOTES, 'UTF-8'),
        (int) ($row['itens_marcados'] ?? 0),
        (int) ($row['evidencias_total'] ?? 0)
    );
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array<string, int>
 */
function relatorios_checklists_summary(array $rows): array
{
    $summary = [
        'checklists_total' => count($rows),
        'nao_conformes' => 0,
        'evidencias_total' => 0,
        'itens_marcados' => 0,
    ];

    foreach ($rows as $row) {
        if (($row['status_conformidade'] ?? '') === 'nao_conforme') {
            $summary['nao_conformes']++;
        }

        $summary['evidencias_total'] += (int) ($row['evidencias_total'] ?? 0);
        $summary['itens_marcados'] += (int) ($row['itens_marcados'] ?? 0);
    }

    return $summary;
}

function relatorios_auditoria_row(array $row): string
{
    return sprintf(
        '<td class="px-6 py-4 text-sm text-slate-700"><div class="font-semibold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>',
        htmlspecialchars((string) $row['event'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['occurred_at'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['action'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['actor_role'] ?? 'sem perfil'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['target_type'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['target_id'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['actor'] ?? 'sistema'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['ip'] ?? 'n/a'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['context_summary'] ?? 'Sem contexto adicional.'), ENT_QUOTES, 'UTF-8')
    );
}

function relatorios_disponibilidade_row(array $row): string
{
    return sprintf(
        '<td class="px-6 py-4"><div class="text-sm font-bold text-slate-900">%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700">%s</td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%s</div><div class="text-xs text-slate-500">%s</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>%d viagem(ns)</div><div class="text-xs text-slate-500">%d manutencao(oes)</div></td>
        <td class="px-6 py-4 text-sm text-slate-700"><div>Ult. viagem: %s</div><div class="text-xs text-slate-500">Ult. abastecimento: %s</div></td>',
        htmlspecialchars((string) $row['placa'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['modelo'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['secretaria_lotada'] ?? 'Nao informada'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) $row['situacao_disponibilidade'], ENT_QUOTES, 'UTF-8'),
        (int) $row['total_viagens'],
        (int) $row['total_manutencoes'],
        htmlspecialchars((string) ($row['ultima_viagem'] ?? '--'), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars((string) ($row['ultimo_abastecimento'] ?? '--'), ENT_QUOTES, 'UTF-8')
    );
}
