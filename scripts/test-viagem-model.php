<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../backend/models/VeiculoModel.php';
require_once __DIR__ . '/../backend/models/ViagemModel.php';

$veiculoModel = new VeiculoModel();
$motoristaModel = new MotoristaModel();
$viagemModel = new ViagemModel();

global $pdo;

$placa = 'VIA1G13';
$modelo = 'Veiculo Teste Viagem';
$cpf = '55566677788';
$cnh = 'VIAGEM12345';

$pdo->prepare('DELETE FROM viagens WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa = ?)')->execute([$placa]);
$pdo->prepare('DELETE FROM motoristas WHERE cpf = ? OR cnh_numero = ?')->execute([$cpf, $cnh]);
$pdo->prepare('DELETE FROM veiculos WHERE placa = ?')->execute([$placa]);

$veiculoId = (int) $veiculoModel->addVeiculo($placa, $modelo, 'ativo');

$motoristaModel->create([
    'nome' => 'Motorista Viagem',
    'cpf' => $cpf,
    'telefone' => '62997776655',
    'secretaria' => 'Secretaria de Educacao',
    'cnh_numero' => $cnh,
    'cnh_categoria' => 'D',
    'cnh_vencimento' => '2032-12-31',
    'status' => 'ativo',
]);

$stmtMotorista = $pdo->prepare('SELECT id FROM motoristas WHERE cpf = ? LIMIT 1');
$stmtMotorista->execute([$cpf]);
$motoristaId = (int) $stmtMotorista->fetchColumn();

if ($motoristaId <= 0) {
    throw new RuntimeException('Motorista de teste nao foi encontrado para a viagem.');
}

$viagemId = $viagemModel->create([
    'veiculo_id' => $veiculoId,
    'motorista_id' => $motoristaId,
    'secretaria' => 'Secretaria de Educacao',
    'solicitante' => 'Diretoria Administrativa',
    'origem' => 'Patio Central',
    'destino' => 'Secretaria Municipal',
    'finalidade' => 'Entrega de documentos oficiais',
    'data_saida' => '2026-04-05T08:30',
    'data_retorno' => null,
    'km_saida' => 10500,
    'km_chegada' => null,
    'status' => 'em_curso',
    'observacoes' => 'Saida autorizada pela secretaria',
]);

$created = $viagemModel->findById($viagemId);
if ($created === null || $created['status'] !== 'em_curso') {
    throw new RuntimeException('Viagem nao foi criada corretamente.');
}

$viagemModel->update($viagemId, [
    'veiculo_id' => $veiculoId,
    'motorista_id' => $motoristaId,
    'secretaria' => 'Secretaria de Educacao',
    'solicitante' => 'Diretoria Administrativa',
    'origem' => 'Patio Central',
    'destino' => 'Secretaria Municipal',
    'finalidade' => 'Entrega de documentos oficiais',
    'data_saida' => '2026-04-05T08:30',
    'data_retorno' => '2026-04-05T10:15',
    'km_saida' => 10500,
    'km_chegada' => 10548,
    'status' => 'concluida',
    'observacoes' => 'Retorno sem ocorrencias',
]);

$updated = $viagemModel->findById($viagemId);
if ($updated === null || $updated['status'] !== 'concluida' || (int) $updated['km_chegada'] !== 10548) {
    throw new RuntimeException('Viagem nao foi atualizada corretamente.');
}

$historico = $viagemModel->listByFilters([
    'status' => 'concluida',
    'secretaria' => 'Secretaria de Educacao',
]);
if (count($historico) !== 1) {
    throw new RuntimeException('Filtro de historico de viagens nao retornou o volume esperado.');
}

$pdo->prepare('DELETE FROM viagens WHERE id = ?')->execute([$viagemId]);
$pdo->prepare('DELETE FROM motoristas WHERE id = ?')->execute([$motoristaId]);
$pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$veiculoId]);

echo "ViagemModel validado com sucesso.\n";
