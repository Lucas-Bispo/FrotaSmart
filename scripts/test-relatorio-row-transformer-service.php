<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$service = new \FrotaSmart\Application\Services\RelatorioRowTransformerService();

$viagens = $service->withViagemMetrics([
    ['km_saida' => 100, 'km_chegada' => 140],
    ['km_saida' => 200, 'km_chegada' => 150],
]);

$disponibilidade = $service->withDisponibilidadeStatus([
    ['deleted_at' => null, 'status' => 'ativo'],
    ['deleted_at' => null, 'status' => 'manutencao'],
    ['deleted_at' => '2026-04-14 10:00:00', 'status' => 'ativo'],
]);

$auditoria = $service->withAuditContextSummary([
    [
        'context_json' => json_encode([
            'report' => 'auditoria',
            'filters' => ['tipo_alvo=veiculo'],
            'request_uri' => '/relatorios.php',
            'confirmado' => true,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ],
    [
        'context_json' => '',
    ],
]);

if (($viagens[0]['km_percorrido'] ?? null) !== 40 || ! array_key_exists('km_percorrido', $viagens[1]) || $viagens[1]['km_percorrido'] !== null) {
    throw new RuntimeException('Transformador de viagens deveria calcular KM percorrido apenas quando a leitura for valida.');
}

if (($disponibilidade[0]['situacao_disponibilidade'] ?? '') !== 'disponivel_operacao'
    || ($disponibilidade[1]['situacao_disponibilidade'] ?? '') !== 'indisponivel_manutencao'
    || ($disponibilidade[2]['situacao_disponibilidade'] ?? '') !== 'arquivado') {
    throw new RuntimeException('Transformador de disponibilidade deveria classificar corretamente cada situacao.');
}

if (! str_contains((string) ($auditoria[0]['context_summary'] ?? ''), 'report: auditoria')
    || ! str_contains((string) ($auditoria[0]['context_summary'] ?? ''), 'confirmado: sim')
    || str_contains((string) ($auditoria[0]['context_summary'] ?? ''), 'request uri')) {
    throw new RuntimeException('Transformador de auditoria deveria resumir contexto relevante e ignorar request_uri.');
}

if (($auditoria[1]['context_summary'] ?? '') !== 'Sem contexto adicional.') {
    throw new RuntimeException('Transformador de auditoria deveria preencher mensagem padrao sem contexto.');
}

echo "Transformador de linhas de relatorio validado com sucesso.\n";
