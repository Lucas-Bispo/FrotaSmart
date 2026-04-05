<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/ParceiroOperacionalModel.php';

$model = new ParceiroOperacionalModel();

global $pdo;

$cnpj = '12345678000199';

$pdo->prepare('DELETE FROM parceiros_operacionais WHERE cnpj = ?')->execute([$cnpj]);

$parceiroId = $model->create([
    'nome_fantasia' => 'Oficina Teste',
    'razao_social' => 'Oficina Teste LTDA',
    'cnpj' => $cnpj,
    'tipo' => 'oficina',
    'telefone' => '62990001111',
    'endereco' => 'Rua das Oficinas, 100',
    'contato_responsavel' => 'Joao Gestor',
    'status' => 'ativo',
    'observacoes' => 'Parceiro de teste automatizado',
]);

$created = $model->findById($parceiroId);
if ($created === null || $created['nome_fantasia'] !== 'Oficina Teste') {
    throw new RuntimeException('Parceiro operacional nao foi criado corretamente.');
}

$model->update($parceiroId, [
    'nome_fantasia' => 'Posto Teste',
    'razao_social' => 'Posto Teste LTDA',
    'cnpj' => $cnpj,
    'tipo' => 'posto_combustivel',
    'telefone' => '62990002222',
    'endereco' => 'Avenida Central, 200',
    'contato_responsavel' => 'Maria Gestora',
    'status' => 'inativo',
    'observacoes' => 'Parceiro atualizado no teste',
]);

$updated = $model->findById($parceiroId);
if ($updated === null || $updated['tipo'] !== 'posto_combustivel' || $updated['status'] !== 'inativo') {
    throw new RuntimeException('Parceiro operacional nao foi atualizado corretamente.');
}

$ativosPosto = $model->getActiveByTipos(['posto_combustivel']);
if (count($ativosPosto) !== 0) {
    throw new RuntimeException('Parceiros inativos nao deveriam aparecer no filtro ativo.');
}

$pdo->prepare('DELETE FROM parceiros_operacionais WHERE id = ?')->execute([$parceiroId]);

echo "ParceiroOperacionalModel validado com sucesso.\n";
