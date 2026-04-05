<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/ManutencaoModel.php';
require_once __DIR__ . '/../backend/models/VeiculoModel.php';

$veiculoModel = new VeiculoModel();
$manutencaoModel = new ManutencaoModel();

global $pdo;

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
]);

$updated = $manutencaoModel->findById($manutencaoId);
if ($updated === null || $updated['status'] !== 'concluida') {
    throw new RuntimeException('Manutencao nao foi atualizada corretamente.');
}

$veiculoAtual->execute([$veiculoId]);
if ($veiculoAtual->fetchColumn() !== 'ativo') {
    throw new RuntimeException('Veiculo deveria voltar para ativo sem manutencoes abertas.');
}

$pdo->prepare('DELETE FROM manutencoes WHERE id = ?')->execute([$manutencaoId]);
$pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$veiculoId]);

echo "ManutencaoModel validado com sucesso.\n";
