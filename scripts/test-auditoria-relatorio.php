<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/RelatorioOperacionalModel.php';

$connection = \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make();
$model = new RelatorioOperacionalModel($connection);

$targetPrefix = 'AUDIT-TEST-23';

$connection->prepare('DELETE FROM audit_logs WHERE target_id LIKE ?')->execute([$targetPrefix . '%']);

$entries = [
    [
        'event' => 'veiculo.created',
        'action' => 'create',
        'target_type' => 'veiculo',
        'target_id' => $targetPrefix . '-VEICULO',
        'actor' => 'auditoria_admin',
        'actor_role' => 'admin',
        'ip' => '127.0.0.1',
        'occurred_at' => '2026-04-11 10:00:00',
        'context_json' => json_encode(['placa' => 'AUD1234', 'status' => 'ativo'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ],
    [
        'event' => 'viagem.created_blocked',
        'action' => 'blocked',
        'target_type' => 'viagem',
        'target_id' => $targetPrefix . '-VIAGEM',
        'actor' => 'gerente_frota',
        'actor_role' => 'gerente',
        'ip' => '127.0.0.1',
        'occurred_at' => '2026-04-11 10:30:00',
        'context_json' => json_encode(['blocked_reasons' => ['Veiculo em manutencao.']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ],
    [
        'event' => 'relatorio.exported',
        'action' => 'export',
        'target_type' => 'relatorio',
        'target_id' => $targetPrefix . '-RELATORIO',
        'actor' => 'auditor_exec',
        'actor_role' => 'auditor',
        'ip' => '127.0.0.1',
        'occurred_at' => '2026-04-11 11:00:00',
        'context_json' => json_encode(['report' => 'auditoria', 'filters' => ['tipo_alvo' => 'veiculo']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ],
];

$statement = $connection->prepare(
    'INSERT INTO audit_logs (
        event,
        action,
        target_type,
        target_id,
        actor,
        actor_role,
        ip,
        occurred_at,
        context_json
    ) VALUES (
        :event,
        :action,
        :target_type,
        :target_id,
        :actor,
        :actor_role,
        :ip,
        :occurred_at,
        :context_json
    )'
);

foreach ($entries as $entry) {
    $statement->execute($entry);
}

$filters = [
    'data_inicio' => '2026-04-11',
    'data_fim' => '2026-04-11',
    'status' => '',
    'ator' => '',
    'evento' => '',
    'tipo_alvo' => '',
];

$rows = $model->getAuditReport($filters);
$summary = $model->getAuditSummary($filters);
$csv = $model->exportCsv('auditoria', $filters);

if (count($rows) < 3) {
    throw new RuntimeException('Relatorio de auditoria deveria retornar ao menos tres eventos de teste.');
}

if ((int) ($summary['exportacoes'] ?? 0) < 1) {
    throw new RuntimeException('Resumo da auditoria deveria consolidar ao menos uma exportacao.');
}

if ((int) ($summary['bloqueios'] ?? 0) < 1) {
    throw new RuntimeException('Resumo da auditoria deveria consolidar ao menos um bloqueio.');
}

if (! str_contains($csv, 'relatorio.exported') || ! str_contains($csv, 'context_summary')) {
    throw new RuntimeException('Exportacao CSV da auditoria deveria incluir evento e resumo de contexto.');
}

$connection->prepare('DELETE FROM audit_logs WHERE target_id LIKE ?')->execute([$targetPrefix . '%']);

echo "Relatorio de auditoria validado com sucesso.\n";
