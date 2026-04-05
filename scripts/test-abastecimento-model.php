<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/AbastecimentoModel.php';
require_once __DIR__ . '/../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../backend/models/VeiculoModel.php';

$veiculoModel = new VeiculoModel();
$motoristaModel = new MotoristaModel();
$abastecimentoModel = new AbastecimentoModel();

global $pdo;

$placa = 'ABS1T11';
$modelo = 'Veiculo Teste Abastecimento';
$cpf = '11122233344';
$cnh = 'ABAST12345';

$pdo->prepare('DELETE FROM abastecimentos WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa = ?)')->execute([$placa]);
$pdo->prepare('DELETE FROM motoristas WHERE cpf = ? OR cnh_numero = ?')->execute([$cpf, $cnh]);
$pdo->prepare('DELETE FROM veiculos WHERE placa = ?')->execute([$placa]);

$veiculoId = (int) $veiculoModel->addVeiculo($placa, $modelo, 'ativo');

$motoristaModel->create([
    'nome' => 'Motorista Abastecimento',
    'cpf' => $cpf,
    'telefone' => '62998887766',
    'secretaria' => 'Secretaria de Saude',
    'cnh_numero' => $cnh,
    'cnh_categoria' => 'D',
    'cnh_vencimento' => '2031-12-31',
    'status' => 'ativo',
]);

$stmtMotorista = $pdo->prepare('SELECT id FROM motoristas WHERE cpf = ? LIMIT 1');
$stmtMotorista->execute([$cpf]);
$motoristaId = (int) $stmtMotorista->fetchColumn();

if ($motoristaId <= 0) {
    throw new RuntimeException('Motorista de teste nao foi encontrado para o abastecimento.');
}

$abastecimentoId = $abastecimentoModel->create([
    'veiculo_id' => $veiculoId,
    'motorista_id' => $motoristaId,
    'data_abastecimento' => '2026-04-05',
    'posto' => 'Posto Central',
    'tipo_combustivel' => 'diesel_s10',
    'litros' => 55.30,
    'valor_total' => 349.90,
    'km_atual' => 125430,
    'observacoes' => 'Abastecimento completo para operacao urbana',
]);

$created = $abastecimentoModel->findById($abastecimentoId);
if ($created === null || $created['posto'] !== 'Posto Central') {
    throw new RuntimeException('Abastecimento nao foi criado corretamente.');
}

$abastecimentoModel->update($abastecimentoId, [
    'veiculo_id' => $veiculoId,
    'motorista_id' => $motoristaId,
    'data_abastecimento' => '2026-04-06',
    'posto' => 'Posto Central Norte',
    'tipo_combustivel' => 'diesel_s10',
    'litros' => 50.00,
    'valor_total' => 320.00,
    'km_atual' => 125980,
    'observacoes' => 'Ajuste de registro apos conferencia',
]);

$updated = $abastecimentoModel->findById($abastecimentoId);
if ($updated === null || $updated['km_atual'] !== 125980 || $updated['posto'] !== 'Posto Central Norte') {
    throw new RuntimeException('Abastecimento nao foi atualizado corretamente.');
}

$historico = $abastecimentoModel->getAll($veiculoId, '2026-04-01', '2026-04-30');
if (count($historico) !== 1) {
    throw new RuntimeException('Historico filtrado de abastecimentos nao retornou o volume esperado.');
}

$pdo->prepare('DELETE FROM abastecimentos WHERE id = ?')->execute([$abastecimentoId]);
$pdo->prepare('DELETE FROM motoristas WHERE id = ?')->execute([$motoristaId]);
$pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$veiculoId]);

echo "AbastecimentoModel validado com sucesso.\n";
