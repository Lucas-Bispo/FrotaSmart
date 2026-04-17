<?php

declare(strict_types=1);

require_once __DIR__ . '/../frontend/views/helpers/dashboard_view_helpers.php';

$today = new DateTimeImmutable('2026-04-16');
$alertLimit = $today->modify('+30 days');

$pageData = dashboard_build_page_data(
    [
        [
            'status' => 'disponivel',
            'placa' => 'ABC1D23',
            'secretaria_lotada' => 'Saude',
            'licenciamento_vencimento' => '2026-04-18',
        ],
        ['status' => 'em_manutencao'],
        ['status' => 'arquivado'],
    ],
    [
        ['status' => 'ativo', 'cnh_vencimento' => '2026-04-20'],
        ['status' => 'inativo', 'cnh_vencimento' => '2026-07-20'],
    ],
    [
        ['data_abastecimento' => '2026-04-15'],
        ['data_abastecimento' => '2026-04-01'],
    ],
    [
        [
            'tipo' => 'saida',
            'placa' => 'ABC1D23',
            'secretaria' => 'Saude',
            'status_conformidade' => 'nao_conforme',
            'nao_conformidades' => 'Pneu traseiro em alerta.',
            'evidencias_json' => '[{"referencia":"foto_1.jpg"},{"referencia":"foto_2.jpg"}]',
        ],
    ],
    [
        ['secretaria' => 'Saude', 'custo_total_periodo' => 1500.0],
    ],
    [
        ['placa' => 'ABC1D23', 'total_alertas' => 2, 'custo_total_periodo' => 750.0],
    ],
    true,
    'arquivados',
    $today,
    $alertLimit,
    3,
    2,
    1,
    1,
    1,
    1,
    2500.5,
    8.25
);

if (count($pageData['primary_metric_cards'] ?? []) !== 4) {
    throw new RuntimeException('Dashboard deveria montar os cards principais a partir do pacote principal da tela.');
}

if (($pageData['executive_overview_cards'][1]['value'] ?? '') !== 'Saude') {
    throw new RuntimeException('Dashboard deveria consolidar a secretaria de maior custo no pacote executivo.');
}

if (($pageData['fleet_filter_tabs'][1]['is_active'] ?? false) !== true) {
    throw new RuntimeException('Dashboard deveria marcar corretamente a aba ativa do filtro de frota.');
}

if (! in_array('1 CNH(s) vencem nos proximos 30 dias.', $pageData['alertas_operacionais'] ?? [], true)) {
    throw new RuntimeException('Dashboard deveria manter os alertas operacionais dentro do pacote principal da tela.');
}

if (! in_array('1 documento(s) veicular(es) vencem nos proximos 30 dias.', $pageData['alertas_operacionais'] ?? [], true)) {
    throw new RuntimeException('Dashboard deveria sinalizar vencimentos documentais no pacote principal da tela.');
}

if (! in_array('1 checklist(s) registraram nao conformidade no periodo recente.', $pageData['alertas_operacionais'] ?? [], true)) {
    throw new RuntimeException('Dashboard deveria sinalizar nao conformidades de checklist no pacote principal da tela.');
}

if (($pageData['document_pending_rows'][0]['placa'] ?? '') !== 'ABC1D23') {
    throw new RuntimeException('Dashboard deveria preparar as linhas de pendencia documental na camada de helper.');
}

if (($pageData['document_secretaria_rows'][0]['secretaria'] ?? '') !== 'Saude') {
    throw new RuntimeException('Dashboard deveria consolidar pendencias documentais por secretaria.');
}

if (($pageData['checklist_rows'][0]['evidencias'] ?? '') !== '2 evidencia(s)') {
    throw new RuntimeException('Dashboard deveria resumir evidencias dos checklists recentes na camada de helper.');
}

echo "Helpers de view do dashboard validados com sucesso.\n";
