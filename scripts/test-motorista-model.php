<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/MotoristaModel.php';

$model = new MotoristaModel();
$cpf = '99988877766';
$cnh = 'TESTE12345';

global $pdo;

$pdo->prepare('DELETE FROM motoristas WHERE cpf = ? OR cnh_numero = ?')->execute([$cpf, $cnh]);

$model->create([
    'nome' => 'Motorista Teste',
    'cpf' => $cpf,
    'telefone' => '62999990000',
    'secretaria' => 'Secretaria de Transporte',
    'cnh_numero' => $cnh,
    'cnh_categoria' => 'D',
    'cnh_vencimento' => '2030-12-31',
    'status' => 'ativo',
]);

$motoristas = $model->getAllMotoristas();
$created = null;

foreach ($motoristas as $motorista) {
    if (($motorista['cpf'] ?? '') === $cpf) {
        $created = $motorista;
        break;
    }
}

if ($created === null) {
    throw new RuntimeException('Motorista de teste nao foi encontrado apos cadastro.');
}

$vencendoAtivo = $model->countCnhsVencendo(3650);
if ($vencendoAtivo < 1) {
    throw new RuntimeException('Contagem de CNHs vencendo deveria considerar o motorista de teste ativo.');
}

$model->update((int) $created['id'], [
    'nome' => 'Motorista Teste Atualizado',
    'cpf' => $cpf,
    'telefone' => '62999991111',
    'secretaria' => 'Secretaria de Obras',
    'cnh_numero' => $cnh,
    'cnh_categoria' => 'E',
    'cnh_vencimento' => '2031-01-01',
    'status' => 'ferias',
]);

$updated = $model->findById((int) $created['id']);

if ($updated === null || $updated['nome'] !== 'Motorista Teste Atualizado' || $updated['status'] !== 'ferias') {
    throw new RuntimeException('Motorista nao foi atualizado corretamente.');
}

$vencendoAfastado = $model->countCnhsVencendo(3650);
if ($vencendoAfastado !== 0) {
    throw new RuntimeException('Contagem de CNHs vencendo nao deveria considerar motorista fora do status ativo.');
}

$pdo->prepare('DELETE FROM motoristas WHERE id = ?')->execute([(int) $created['id']]);

echo "MotoristaModel validado com sucesso.\n";
