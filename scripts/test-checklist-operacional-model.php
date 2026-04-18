<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/ChecklistOperacionalModel.php';
require_once __DIR__ . '/../backend/models/ViagemModel.php';

$connection = \FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make();
$model = new ChecklistOperacionalModel($connection);
$viagemModel = new ViagemModel($connection);

$secretaria = 'Secretaria Teste Checklist';
$responsavel = 'Responsavel Teste Checklist';
$cleanupVeiculoId = null;
$cleanupMotoristaId = null;
$cleanupViagemId = null;

$veiculo = $connection->query('SELECT id FROM veiculos ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$motorista = $connection->query('SELECT id FROM motoristas ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

if (! is_array($veiculo)) {
    $placa = 'TST' . random_int(1000, 9999);
    $renavam = (string) random_int(10000000000, 99999999999);
    $chassi = 'CHASSITESTE' . random_int(1000, 9999);

    $stmt = $connection->prepare(
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

    $cleanupVeiculoId = (int) $connection->lastInsertId();
    $veiculo = ['id' => $cleanupVeiculoId];
}

if (! is_array($motorista)) {
    $cpf = (string) random_int(10000000000, 99999999999);
    $cnh = 'CNH' . random_int(100000, 999999);

    $stmt = $connection->prepare(
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

    $cleanupMotoristaId = (int) $connection->lastInsertId();
    $motorista = ['id' => $cleanupMotoristaId];
}

$connection->prepare('DELETE FROM checklists_operacionais WHERE secretaria = ?')->execute([$secretaria]);

$cleanupViagemId = $viagemModel->create([
    'veiculo_id' => (int) $veiculo['id'],
    'motorista_id' => (int) $motorista['id'],
    'secretaria' => $secretaria,
    'solicitante' => $responsavel,
    'origem' => 'Garagem Central',
    'destino' => 'Centro Administrativo',
    'finalidade' => 'Viagem para validar checklist automatizado.',
    'data_saida' => '2026-04-17 07:45:00',
    'data_retorno' => null,
    'km_saida' => 1000,
    'km_chegada' => null,
    'status' => 'em_curso',
    'observacoes' => null,
]);

$checklistId = $model->create([
    'tipo' => 'saida',
    'viagem_id' => $cleanupViagemId,
    'veiculo_id' => (int) $veiculo['id'],
    'motorista_id' => (int) $motorista['id'],
    'secretaria' => $secretaria,
    'responsavel_operacao' => $responsavel,
    'status_conformidade' => 'nao_conforme',
    'aceite_responsavel' => 1,
    'realizado_em' => '2026-04-17 08:30:00',
    'itens_json' => json_encode([
        ['codigo' => 'documentacao', 'label' => 'Documentacao obrigatoria', 'checked' => true, 'observacao' => null],
        ['codigo' => 'pneus', 'label' => 'Pneus e rodas', 'checked' => false, 'observacao' => 'Desgaste visivel no pneu traseiro.'],
    ], JSON_UNESCAPED_UNICODE),
    'evidencias_json' => json_encode([
        ['referencia' => 'foto_saida_001.jpg'],
        ['referencia' => 'protocolo-ocorrencia-01'],
    ], JSON_UNESCAPED_UNICODE),
    'nao_conformidades' => 'Pneu traseiro com desgaste acentuado.',
    'evidencia_referencia' => 'foto_saida_001.jpg | protocolo-ocorrencia-01',
    'observacoes' => 'Checklist criado no teste automatizado.',
]);

$created = $model->findById($checklistId);
if ($created === null || $created['status_conformidade'] !== 'nao_conforme' || (int) ($created['viagem_id'] ?? 0) !== $cleanupViagemId) {
    throw new RuntimeException('Checklist operacional nao foi criado corretamente.');
}

$createdEvidence = json_decode((string) ($created['evidencias_json'] ?? '[]'), true);
if (! is_array($createdEvidence) || count($createdEvidence) !== 2) {
    throw new RuntimeException('Checklist operacional deveria persistir multiplas evidencias.');
}

$model->update($checklistId, [
    'tipo' => 'retorno',
    'viagem_id' => null,
    'veiculo_id' => (int) $veiculo['id'],
    'motorista_id' => (int) $motorista['id'],
    'secretaria' => $secretaria,
    'responsavel_operacao' => $responsavel,
    'status_conformidade' => 'conforme',
    'aceite_responsavel' => 0,
    'realizado_em' => '2026-04-17 18:10:00',
    'itens_json' => json_encode([
        ['codigo' => 'documentacao', 'label' => 'Documentacao obrigatoria', 'checked' => true, 'observacao' => null],
        ['codigo' => 'limpeza', 'label' => 'Condicoes gerais e limpeza', 'checked' => true, 'observacao' => 'Veiculo retornou limpo.'],
    ], JSON_UNESCAPED_UNICODE),
    'evidencias_json' => json_encode([
        ['referencia' => 'checklist-retorno-protocolo-01'],
        ['referencia' => 'foto_retorno_002.jpg'],
        ['referencia' => 'termo_assinado.pdf'],
    ], JSON_UNESCAPED_UNICODE),
    'nao_conformidades' => null,
    'evidencia_referencia' => 'checklist-retorno-protocolo-01 | foto_retorno_002.jpg | termo_assinado.pdf',
    'observacoes' => 'Checklist ajustado no teste automatizado.',
]);

$updated = $model->findById($checklistId);
if ($updated === null || $updated['tipo'] !== 'retorno' || $updated['status_conformidade'] !== 'conforme' || ($updated['viagem_id'] ?? null) !== null) {
    throw new RuntimeException('Checklist operacional nao foi atualizado corretamente.');
}

$updatedEvidence = json_decode((string) ($updated['evidencias_json'] ?? '[]'), true);
if (! is_array($updatedEvidence) || count($updatedEvidence) !== 3) {
    throw new RuntimeException('Checklist operacional deveria atualizar a colecao de evidencias.');
}

$filtrados = $model->listByFilters([
    'tipo' => 'retorno',
    'status' => 'conforme',
    'secretaria' => $secretaria,
]);

if (count($filtrados) !== 1 || (int) ($filtrados[0]['id'] ?? 0) !== $checklistId) {
    throw new RuntimeException('Filtro nomeado de checklist nao retornou o registro esperado.');
}

$connection->prepare('DELETE FROM checklists_operacionais WHERE id = ?')->execute([$checklistId]);

if ($cleanupViagemId !== null) {
    $connection->prepare('DELETE FROM viagens WHERE id = ?')->execute([$cleanupViagemId]);
}

if ($cleanupMotoristaId !== null) {
    $connection->prepare('DELETE FROM motoristas WHERE id = ?')->execute([$cleanupMotoristaId]);
}

if ($cleanupVeiculoId !== null) {
    $connection->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$cleanupVeiculoId]);
}

echo "ChecklistOperacionalModel validado com sucesso.\n";
