<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/OperacaoFrotaGuard.php';
require_once __DIR__ . '/../backend/models/ManutencaoModel.php';
require_once __DIR__ . '/../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../backend/models/VeiculoModel.php';

global $pdo;

$veiculoModel = new VeiculoModel($pdo);
$motoristaModel = new MotoristaModel($pdo);
$manutencaoModel = new ManutencaoModel($pdo);
$guard = new OperacaoFrotaGuard($veiculoModel, $motoristaModel, $manutencaoModel);

$placa = 'OPR2A11';
$modelo = 'Veiculo Teste Regras';
$cpf = '55566677788';
$cnh = 'REGRA12345';

$pdo->prepare('DELETE FROM manutencoes WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa = ?)')->execute([$placa]);
$pdo->prepare('DELETE FROM abastecimentos WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa = ?)')->execute([$placa]);
$pdo->prepare('DELETE FROM viagens WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa = ?)')->execute([$placa]);
$pdo->prepare('DELETE FROM motoristas WHERE cpf = ? OR cnh_numero = ?')->execute([$cpf, $cnh]);
$pdo->prepare('DELETE FROM veiculos WHERE placa = ?')->execute([$placa]);

$veiculoId = (int) $veiculoModel->addVeiculo($placa, $modelo, 'ativo', null, null, null, 'van', 'diesel', 'Educacao', 10000);

$motoristaModel->create([
    'nome' => 'Motorista Regras',
    'cpf' => $cpf,
    'telefone' => '62995554433',
    'secretaria' => 'Secretaria de Educacao',
    'cnh_numero' => $cnh,
    'cnh_categoria' => 'D',
    'cnh_vencimento' => '2026-04-20',
    'status' => 'ativo',
]);

$stmtMotorista = $pdo->prepare('SELECT id FROM motoristas WHERE cpf = ? LIMIT 1');
$stmtMotorista->execute([$cpf]);
$motoristaId = (int) $stmtMotorista->fetchColumn();

if ($motoristaId <= 0) {
    throw new RuntimeException('Motorista de teste nao foi encontrado para validar as regras operacionais.');
}

$manutencaoId = $manutencaoModel->create([
    'veiculo_id' => $veiculoId,
    'data_abertura' => '2026-03-01',
    'data_conclusao' => '2026-03-02',
    'tipo' => 'preventiva',
    'status' => 'concluida',
    'fornecedor' => 'Oficina da Frota',
    'parceiro_id' => null,
    'custo_estimado' => 350.00,
    'custo_final' => 360.00,
    'descricao' => 'Revisao preventiva agendada',
    'observacoes' => 'Plano de teste para bloqueio operacional',
    'km_referencia' => 10000,
    'km_proxima_preventiva' => 11000,
    'data_proxima_preventiva' => '2026-04-01',
    'recorrencia_dias' => 30,
    'recorrencia_km' => 1000,
]);

$tripAnalysis = $guard->analyzeTrip($veiculoId, $motoristaId, '2026-04-10T08:00', 12050);

if ($tripAnalysis['blocked'] === []) {
    throw new RuntimeException('Viagem deveria ser bloqueada quando a preventiva estiver vencida.');
}

$tripBlockedSummary = implode(' ', $tripAnalysis['blocked']);
if (stripos($tripBlockedSummary, 'preventiva vencida') === false) {
    throw new RuntimeException('Bloqueio de viagem deveria mencionar a preventiva vencida.');
}

if ($tripAnalysis['warnings'] === []) {
    throw new RuntimeException('Viagem deveria alertar sobre CNH proxima do vencimento.');
}

$tripWarningSummary = implode(' ', $tripAnalysis['warnings']);
if (stripos($tripWarningSummary, 'CNH') === false) {
    throw new RuntimeException('Alerta de viagem deveria mencionar a CNH proxima do vencimento.');
}

$fuelAnalysis = $guard->analyzeFuel($veiculoId, $motoristaId, '2026-04-10', 12050);

if ($fuelAnalysis['blocked'] !== []) {
    throw new RuntimeException('Abastecimento nao deveria ser bloqueado apenas por preventiva vencida.');
}

$fuelWarningSummary = implode(' ', $fuelAnalysis['warnings']);
if (stripos($fuelWarningSummary, 'Preventiva do veiculo') === false) {
    throw new RuntimeException('Abastecimento deveria alertar sobre preventiva vencida.');
}

$pdo->prepare('UPDATE veiculos SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$veiculoId]);
$archivedAnalysis = $guard->analyzeTrip($veiculoId, $motoristaId, '2026-04-10T09:00', 12060);

if ($archivedAnalysis['blocked'] === []) {
    throw new RuntimeException('Veiculo arquivado deveria bloquear a operacao.');
}

$archivedBlockedSummary = implode(' ', $archivedAnalysis['blocked']);
if (stripos($archivedBlockedSummary, 'arquivado') === false) {
    throw new RuntimeException('Bloqueio deveria mencionar que o veiculo esta arquivado.');
}

$pdo->prepare('DELETE FROM manutencoes WHERE id = ?')->execute([$manutencaoId]);
$pdo->prepare('DELETE FROM motoristas WHERE id = ?')->execute([$motoristaId]);
$pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$veiculoId]);

echo "OperacaoFrotaGuard validado com sucesso.\n";
