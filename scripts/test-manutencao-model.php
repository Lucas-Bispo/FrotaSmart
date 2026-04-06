<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/ManutencaoModel.php';
require_once __DIR__ . '/../backend/models/VeiculoModel.php';

$veiculoModel = new VeiculoModel();
$manutencaoModel = new ManutencaoModel();

global $pdo;

try {
    $pdo->exec("ALTER TABLE manutencoes ADD COLUMN km_referencia INT NULL AFTER data_conclusao");
} catch (Throwable) {
}
try {
    $pdo->exec("ALTER TABLE manutencoes ADD COLUMN km_proxima_preventiva INT NULL AFTER km_referencia");
} catch (Throwable) {
}
try {
    $pdo->exec("ALTER TABLE manutencoes ADD COLUMN data_proxima_preventiva DATE NULL AFTER km_proxima_preventiva");
} catch (Throwable) {
}
try {
    $pdo->exec("ALTER TABLE manutencoes ADD COLUMN recorrencia_dias INT NULL AFTER data_proxima_preventiva");
} catch (Throwable) {
}
try {
    $pdo->exec("ALTER TABLE manutencoes ADD COLUMN recorrencia_km INT NULL AFTER recorrencia_dias");
} catch (Throwable) {
}

$placa = 'MNT1A23';
$modelo = 'Veiculo Teste Manutencao';

$pdo->prepare('DELETE FROM manutencoes WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa = ?)')->execute([$placa]);
$pdo->prepare('DELETE FROM veiculos WHERE placa = ?')->execute([$placa]);

$veiculoId = (int) $veiculoModel->addVeiculo($placa, $modelo, 'ativo');

$manutencaoId = $manutencaoModel->create([
    'veiculo_id' => $veiculoId,
    'data_abertura' => '2026-04-05',
    'data_conclusao' => null,
    'tipo' => 'corretiva',
    'status' => 'aberta',
    'fornecedor' => 'Oficina Central',
    'custo_estimado' => 500.00,
    'custo_final' => 0.00,
    'descricao' => 'Troca de embreagem',
    'observacoes' => 'Veiculo indisponivel durante o reparo',
    'km_referencia' => null,
    'km_proxima_preventiva' => null,
    'data_proxima_preventiva' => null,
    'recorrencia_dias' => null,
    'recorrencia_km' => null,
]);

$created = $manutencaoModel->findById($manutencaoId);
if ($created === null || $created['status'] !== 'aberta') {
    throw new RuntimeException('Manutencao nao foi criada corretamente.');
}

$veiculoAtual = $pdo->prepare('SELECT status FROM veiculos WHERE id = ?');
$veiculoAtual->execute([$veiculoId]);
if ($veiculoAtual->fetchColumn() !== 'manutencao') {
    throw new RuntimeException('Veiculo deveria estar em manutencao com OS aberta.');
}

$manutencaoModel->update($manutencaoId, [
    'veiculo_id' => $veiculoId,
    'data_abertura' => '2026-04-05',
    'data_conclusao' => '2026-04-10',
    'tipo' => 'corretiva',
    'status' => 'concluida',
    'fornecedor' => 'Oficina Central',
    'custo_estimado' => 500.00,
    'custo_final' => 650.00,
    'descricao' => 'Troca de embreagem',
    'observacoes' => 'Servico concluido com substituicao de pecas',
    'km_referencia' => null,
    'km_proxima_preventiva' => null,
    'data_proxima_preventiva' => null,
    'recorrencia_dias' => null,
    'recorrencia_km' => null,
]);

$updated = $manutencaoModel->findById($manutencaoId);
if ($updated === null || $updated['status'] !== 'concluida') {
    throw new RuntimeException('Manutencao nao foi atualizada corretamente.');
}

$veiculoAtual->execute([$veiculoId]);
if ($veiculoAtual->fetchColumn() !== 'ativo') {
    throw new RuntimeException('Veiculo deveria voltar para ativo sem manutencoes abertas.');
}

$preventivaId = $manutencaoModel->create([
    'veiculo_id' => $veiculoId,
    'data_abertura' => date('Y-m-d', strtotime('-10 days')),
    'data_conclusao' => date('Y-m-d', strtotime('-5 days')),
    'tipo' => 'preventiva',
    'status' => 'concluida',
    'fornecedor' => 'Oficina Preventiva',
    'custo_estimado' => 300.00,
    'custo_final' => 320.00,
    'descricao' => 'Revisao programada',
    'observacoes' => 'Plano por data e por km',
    'km_referencia' => 10000,
    'km_proxima_preventiva' => 11000,
    'data_proxima_preventiva' => date('Y-m-d', strtotime('+7 days')),
    'recorrencia_dias' => 180,
    'recorrencia_km' => 1000,
]);

$preventiva = $manutencaoModel->findById($preventivaId);
if ($preventiva === null || ($preventiva['preventiva_alerta_status'] ?? '') !== 'proxima') {
    throw new RuntimeException('Preventiva deveria aparecer como proxima.');
}

$alertasPreventivos = $manutencaoModel->getPreventiveAlerts();
if (count($alertasPreventivos) < 1) {
    throw new RuntimeException('Modelo deveria retornar alertas preventivos.');
}

$pdo->prepare('DELETE FROM manutencoes WHERE id = ?')->execute([$manutencaoId]);
$pdo->prepare('DELETE FROM manutencoes WHERE id = ?')->execute([$preventivaId]);
$pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$veiculoId]);

echo "ManutencaoModel validado com sucesso.\n";
