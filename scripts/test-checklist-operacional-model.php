<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/ChecklistOperacionalModel.php';

$model = new ChecklistOperacionalModel();

global $pdo;

$secretaria = 'Secretaria Teste Checklist';
$responsavel = 'Responsavel Teste Checklist';
$cleanupVeiculoId = null;
$cleanupMotoristaId = null;

$veiculo = $pdo->query('SELECT id FROM veiculos ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$motorista = $pdo->query('SELECT id FROM motoristas ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

if (! is_array($veiculo)) {
    $placa = 'TST' . random_int(1000, 9999);
    $renavam = (string) random_int(10000000000, 99999999999);
    $chassi = 'CHASSITESTE' . random_int(1000, 9999);

    $stmt = $pdo->prepare(
        "INSERT INTO veiculos (
            placa,
            modelo,
            renavam,
            chassi,
            ano_fabricacao,
            tipo,
            combustivel,
            secretaria_lotada,
            quilometragem_inicial,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $placa,
        'Veiculo Checklist Teste',
        $renavam,
        $chassi,
        2024,
        'utilitario',
        'flex',
        $secretaria,
        1000,
        'ativo',
    ]);

    $cleanupVeiculoId = (int) $pdo->lastInsertId();
    $veiculo = ['id' => $cleanupVeiculoId];
}

if (! is_array($motorista)) {
    $cpf = (string) random_int(10000000000, 99999999999);
    $cnh = 'CNH' . random_int(100000, 999999);

    $stmt = $pdo->prepare(
        "INSERT INTO motoristas (
            nome,
            cpf,
            telefone,
            secretaria,
            cnh_numero,
            cnh_categoria,
            cnh_vencimento,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        'Motorista Checklist Teste',
        $cpf,
        '62999990000',
        $secretaria,
        $cnh,
        'B',
        '2027-12-31',
        'ativo',
    ]);

    $cleanupMotoristaId = (int) $pdo->lastInsertId();
    $motorista = ['id' => $cleanupMotoristaId];
}

$pdo->prepare('DELETE FROM checklists_operacionais WHERE secretaria = ?')->execute([$secretaria]);

$checklistId = $model->create([
    'tipo' => 'saida',
    'veiculo_id' => (int) $veiculo['id'],
    'motorista_id' => (int) $motorista['id'],
    'secretaria' => $secretaria,
    'responsavel_operacao' => $responsavel,
    'status_conformidade' => 'nao_conforme',
    'aceite_responsavel' => 1,
    'realizado_em' => '2026-04-17 08:30:00',
    'nao_conformidades' => 'Pneu traseiro com desgaste acentuado.',
    'evidencia_referencia' => 'foto_saida_001.jpg',
    'observacoes' => 'Checklist criado no teste automatizado.',
]);

$created = $model->findById($checklistId);
if ($created === null || $created['status_conformidade'] !== 'nao_conforme') {
    throw new RuntimeException('Checklist operacional nao foi criado corretamente.');
}

$model->update($checklistId, [
    'tipo' => 'retorno',
    'veiculo_id' => (int) $veiculo['id'],
    'motorista_id' => (int) $motorista['id'],
    'secretaria' => $secretaria,
    'responsavel_operacao' => $responsavel,
    'status_conformidade' => 'conforme',
    'aceite_responsavel' => 0,
    'realizado_em' => '2026-04-17 18:10:00',
    'nao_conformidades' => null,
    'evidencia_referencia' => 'checklist-retorno-protocolo-01',
    'observacoes' => 'Checklist ajustado no teste automatizado.',
]);

$updated = $model->findById($checklistId);
if ($updated === null || $updated['tipo'] !== 'retorno' || $updated['status_conformidade'] !== 'conforme') {
    throw new RuntimeException('Checklist operacional nao foi atualizado corretamente.');
}

$filtrados = $model->listByFilters([
    'tipo' => 'retorno',
    'status' => 'conforme',
    'secretaria' => $secretaria,
]);

if (count($filtrados) !== 1 || (int) ($filtrados[0]['id'] ?? 0) !== $checklistId) {
    throw new RuntimeException('Filtro nomeado de checklist nao retornou o registro esperado.');
}

$pdo->prepare('DELETE FROM checklists_operacionais WHERE id = ?')->execute([$checklistId]);

if ($cleanupMotoristaId !== null) {
    $pdo->prepare('DELETE FROM motoristas WHERE id = ?')->execute([$cleanupMotoristaId]);
}

if ($cleanupVeiculoId !== null) {
    $pdo->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$cleanupVeiculoId]);
}

echo "ChecklistOperacionalModel validado com sucesso.\n";
