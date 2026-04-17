<?php

declare(strict_types=1);

require_once __DIR__ . '/../frontend/views/helpers/dashboard_view_helpers.php';

$secretariaRows = dashboard_build_secretaria_rows([
    [
        'secretaria' => 'Saude',
        'frota_ativa' => 4,
        'frota_operacao' => 3,
        'motoristas_ativos' => 5,
        'custo_total_periodo' => 15250.75,
        'viagens_periodo' => 12,
        'km_viagens_periodo' => 1840,
        'disponibilidade_percentual' => 87.5,
        'abastecimentos_periodo' => 9,
        'alertas_total' => 2,
    ],
]);

if (($secretariaRows[0]['custo_total'] ?? '') !== 'R$ 15.250,75') {
    throw new RuntimeException('Dashboard deveria formatar o custo da secretaria no helper de tabela.');
}

if (($secretariaRows[0]['alertas_class'] ?? '') !== 'text-amber-700') {
    throw new RuntimeException('Dashboard deveria destacar secretarias com alertas ativos.');
}

$vehicleRows = dashboard_build_executive_vehicle_rows([
    [
        'placa' => 'ABC1D23',
        'modelo' => 'Sprinter',
        'secretaria_lotada' => 'Saude',
        'viagens_periodo' => 6,
        'km_viagens_periodo' => 950,
        'abastecimentos_periodo' => 4,
        'custo_total_periodo' => 4500.4,
        'gasto_abastecimento_periodo' => 1800.1,
        'custo_manutencao_periodo' => 700,
        'preventiva_status' => 'vencida',
        'deleted_at' => '2026-04-10 08:00:00',
        'total_alertas' => 3,
        'preventiva_resumo' => 'Preventiva atrasada ha 10 dias',
    ],
]);

if (($vehicleRows[0]['preventiva_badge_label'] ?? '') !== 'Preventiva vencida') {
    throw new RuntimeException('Dashboard deveria mapear o badge executivo do veiculo no helper.');
}

if (($vehicleRows[0]['exibir_arquivado'] ?? false) !== true) {
    throw new RuntimeException('Dashboard deveria indicar quando o veiculo executivo esta arquivado.');
}

$refuelRows = dashboard_build_recent_refuel_rows([
    [
        'placa' => 'XYZ9K88',
        'modelo' => 'Ranger',
        'motorista_nome' => 'Carlos',
        'secretaria' => 'Obras',
        'tipo_combustivel' => 'diesel_s10',
        'data_abastecimento' => '2026-04-16',
        'valor_total' => 680.5,
        'litros' => 120.35,
    ],
]);

if (($refuelRows[0]['combustivel'] ?? '') !== 'DIESEL S10') {
    throw new RuntimeException('Dashboard deveria normalizar o tipo de combustivel da tabela de abastecimentos.');
}

if (($refuelRows[0]['litros'] ?? '') !== '120,35 L') {
    throw new RuntimeException('Dashboard deveria formatar litros no helper da tabela de abastecimentos.');
}

$documentRows = dashboard_build_document_pending_rows([
    [
        'placa' => 'DOC1A23',
        'secretaria_lotada' => 'Saude',
        'licenciamento_vencimento' => '2026-04-10',
        'seguro_vencimento' => '2026-04-25',
    ],
], new DateTimeImmutable('2026-04-16'), new DateTimeImmutable('2026-05-16'));

if (($documentRows[0]['status_badge'] ?? '') !== 'Vencido') {
    throw new RuntimeException('Dashboard deveria priorizar badge de documento vencido nas pendencias.');
}

if (strpos((string) ($documentRows[0]['pendencias'] ?? ''), 'Seguro vence em 2026-04-25') === false) {
    throw new RuntimeException('Dashboard deveria resumir as pendencias documentais por veiculo.');
}

$documentSecretariaRows = dashboard_build_document_secretaria_rows([
    [
        'placa' => 'DOC1A23',
        'secretaria_lotada' => 'Saude',
        'licenciamento_vencimento' => '2026-04-10',
    ],
    [
        'placa' => 'DOC1A24',
        'secretaria_lotada' => 'Saude',
        'seguro_vencimento' => '2026-04-25',
    ],
    [
        'placa' => 'DOC1B23',
        'secretaria_lotada' => 'Obras',
        'crlv_vencimento' => '2026-04-20',
    ],
], new DateTimeImmutable('2026-04-16'), new DateTimeImmutable('2026-05-16'));

if (($documentSecretariaRows[0]['secretaria'] ?? '') !== 'Saude') {
    throw new RuntimeException('Dashboard deveria ordenar primeiro a secretaria com mais pendencias documentais.');
}

if (($documentSecretariaRows[0]['veiculos_afetados'] ?? '') !== '2 veiculo(s)') {
    throw new RuntimeException('Dashboard deveria consolidar o total de veiculos afetados por secretaria.');
}

echo "Helpers de tabela do dashboard validados com sucesso.\n";
