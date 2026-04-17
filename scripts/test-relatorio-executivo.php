<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/RelatorioOperacionalModel.php';
require_once __DIR__ . '/../backend/models/AbastecimentoModel.php';
require_once __DIR__ . '/../backend/models/ManutencaoModel.php';
require_once __DIR__ . '/../backend/models/MotoristaModel.php';
require_once __DIR__ . '/../backend/models/VeiculoModel.php';
require_once __DIR__ . '/../backend/models/ViagemModel.php';

$relatorioModel = new RelatorioOperacionalModel(\FrotaSmart\Infrastructure\Config\PdoConnectionFactory::make());
$abastecimentoModel = new AbastecimentoModel();
$manutencaoModel = new ManutencaoModel();
$motoristaModel = new MotoristaModel();
$veiculoModel = new VeiculoModel();
$viagemModel = new ViagemModel();

global $pdo;

$placaSaude = 'PEX1A22';
$placaEducacao = 'PEX1B22';
$cpfSaude = '90111111111';
$cpfEducacao = '90222222222';
$cnhSaude = 'EXEC12345';
$cnhEducacao = 'EXEC67890';

$pdo->prepare('DELETE FROM abastecimentos WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa IN (?, ?))')->execute([$placaSaude, $placaEducacao]);
$pdo->prepare('DELETE FROM manutencoes WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa IN (?, ?))')->execute([$placaSaude, $placaEducacao]);
$pdo->prepare('DELETE FROM viagens WHERE veiculo_id IN (SELECT id FROM veiculos WHERE placa IN (?, ?))')->execute([$placaSaude, $placaEducacao]);
$pdo->prepare('DELETE FROM motoristas WHERE cpf IN (?, ?) OR cnh_numero IN (?, ?)')->execute([$cpfSaude, $cpfEducacao, $cnhSaude, $cnhEducacao]);
$pdo->prepare('DELETE FROM veiculos WHERE placa IN (?, ?)')->execute([$placaSaude, $placaEducacao]);

$veiculoSaudeId = (int) $veiculoModel->addVeiculo($placaSaude, 'Ambulancia Executiva', 'ativo', null, null, 2023, 'ambulancia', 'diesel_s10', 'Saude', 10000);
$veiculoEducacaoId = (int) $veiculoModel->addVeiculo($placaEducacao, 'Onibus Escolar Executivo', 'ativo', null, null, 2022, 'onibus', 'diesel_s10', 'Educacao', 20000);

$motoristaModel->create([
    'nome' => 'Motorista Saude Executivo',
    'cpf' => $cpfSaude,
    'telefone' => '62990000001',
    'secretaria' => 'Saude',
    'cnh_numero' => $cnhSaude,
    'cnh_categoria' => 'D',
    'cnh_vencimento' => '2030-01-01',
    'status' => 'ativo',
]);

$motoristaModel->create([
    'nome' => 'Motorista Educacao Executivo',
    'cpf' => $cpfEducacao,
    'telefone' => '62990000002',
    'secretaria' => 'Educacao',
    'cnh_numero' => $cnhEducacao,
    'cnh_categoria' => 'D',
    'cnh_vencimento' => '2030-01-01',
    'status' => 'ativo',
]);

$stmtMotorista = $pdo->prepare('SELECT id, cpf FROM motoristas WHERE cpf IN (?, ?)');
$stmtMotorista->execute([$cpfSaude, $cpfEducacao]);
$motoristas = $stmtMotorista->fetchAll(PDO::FETCH_ASSOC);

$motoristaSaudeId = 0;
$motoristaEducacaoId = 0;

foreach ($motoristas as $motorista) {
    if (($motorista['cpf'] ?? '') === $cpfSaude) {
        $motoristaSaudeId = (int) $motorista['id'];
    }

    if (($motorista['cpf'] ?? '') === $cpfEducacao) {
        $motoristaEducacaoId = (int) $motorista['id'];
    }
}

if ($motoristaSaudeId <= 0 || $motoristaEducacaoId <= 0) {
    throw new RuntimeException('Motoristas de teste nao foram encontrados para o painel executivo.');
}

$abastecimentoModel->create([
    'veiculo_id' => $veiculoSaudeId,
    'motorista_id' => $motoristaSaudeId,
    'data_abastecimento' => '2026-04-08',
    'posto' => 'Posto Saude',
    'tipo_combustivel' => 'diesel_s10',
    'litros' => 40.00,
    'valor_total' => 300.00,
    'km_atual' => 10040,
    'observacoes' => 'Abastecimento executivo de saude',
]);

$abastecimentoModel->create([
    'veiculo_id' => $veiculoSaudeId,
    'motorista_id' => $motoristaSaudeId,
    'data_abastecimento' => '2026-04-09',
    'posto' => 'Posto Saude 2',
    'tipo_combustivel' => 'diesel_s10',
    'litros' => 35.00,
    'valor_total' => 315.00,
    'km_atual' => 10200,
    'observacoes' => 'Nova leitura para consolidar consumo',
]);

$abastecimentoModel->create([
    'veiculo_id' => $veiculoEducacaoId,
    'motorista_id' => $motoristaEducacaoId,
    'data_abastecimento' => '2026-04-09',
    'posto' => 'Posto Educacao',
    'tipo_combustivel' => 'diesel_s10',
    'litros' => 50.00,
    'valor_total' => 400.00,
    'km_atual' => 20120,
    'observacoes' => 'Abastecimento executivo de educacao',
]);

$manutencaoModel->create([
    'veiculo_id' => $veiculoSaudeId,
    'data_abertura' => '2026-03-01',
    'data_conclusao' => '2026-03-02',
    'tipo' => 'preventiva',
    'status' => 'concluida',
    'fornecedor' => 'Oficina Saude',
    'parceiro_id' => null,
    'custo_estimado' => 500.00,
    'custo_final' => 500.00,
    'descricao' => 'Plano preventivo vencido para leitura executiva',
    'observacoes' => 'Teste do painel por secretaria',
    'km_referencia' => 10000,
    'km_proxima_preventiva' => 10100,
    'data_proxima_preventiva' => '2026-04-01',
    'recorrencia_dias' => 30,
    'recorrencia_km' => 1000,
]);

$manutencaoModel->create([
    'veiculo_id' => $veiculoEducacaoId,
    'data_abertura' => '2026-04-10',
    'data_conclusao' => null,
    'tipo' => 'corretiva',
    'status' => 'aberta',
    'fornecedor' => 'Oficina Educacao',
    'parceiro_id' => null,
    'custo_estimado' => 900.00,
    'custo_final' => 0.0,
    'descricao' => 'Manutencao aberta para leitura executiva',
    'observacoes' => 'Teste do painel por veiculo',
    'km_referencia' => null,
    'km_proxima_preventiva' => null,
    'data_proxima_preventiva' => null,
    'recorrencia_dias' => null,
    'recorrencia_km' => null,
]);

$viagemModel->create([
    'veiculo_id' => $veiculoSaudeId,
    'motorista_id' => $motoristaSaudeId,
    'secretaria' => 'Saude',
    'solicitante' => 'Coordenacao UBS',
    'origem' => 'Base Central',
    'destino' => 'UBS Norte',
    'finalidade' => 'Atendimento externo',
    'data_saida' => '2026-04-09 08:00:00',
    'data_retorno' => '2026-04-09 10:00:00',
    'km_saida' => 10120,
    'km_chegada' => 10180,
    'status' => 'concluida',
    'observacoes' => 'Viagem de teste para saude',
]);

$viagemModel->create([
    'veiculo_id' => $veiculoEducacaoId,
    'motorista_id' => $motoristaEducacaoId,
    'secretaria' => 'Educacao',
    'solicitante' => 'Coordenacao Escolar',
    'origem' => 'Garagem',
    'destino' => 'Escola Municipal',
    'finalidade' => 'Transporte escolar',
    'data_saida' => '2026-04-10 06:30:00',
    'data_retorno' => '2026-04-10 08:30:00',
    'km_saida' => 20020,
    'km_chegada' => 20100,
    'status' => 'concluida',
    'observacoes' => 'Viagem de teste para educacao',
]);

$porSecretaria = $relatorioModel->getExecutiveSummaryBySecretaria([
    'data_inicio' => '2026-04-01',
    'data_fim' => '2026-04-30',
]);
$porVeiculo = $relatorioModel->getExecutiveSummaryByVeiculo([
    'data_inicio' => '2026-04-01',
    'data_fim' => '2026-04-30',
    'limit' => 10,
]);

$saude = null;
$educacao = null;

foreach ($porSecretaria as $item) {
    if (($item['secretaria'] ?? '') === 'Saude') {
        $saude = $item;
    }

    if (($item['secretaria'] ?? '') === 'Educacao') {
        $educacao = $item;
    }
}

if (! is_array($saude) || (int) ($saude['viagens_periodo'] ?? 0) < 1 || (float) ($saude['gasto_abastecimento_periodo'] ?? 0) <= 0) {
    throw new RuntimeException('Painel executivo por secretaria nao consolidou corretamente os dados da Saude.');
}

if ((int) ($saude['preventivas_vencidas'] ?? 0) < 1) {
    throw new RuntimeException('Painel executivo por secretaria deveria sinalizar preventiva vencida na Saude.');
}

if (! is_array($educacao) || (int) ($educacao['manutencoes_abertas'] ?? 0) < 1) {
    throw new RuntimeException('Painel executivo por secretaria deveria consolidar manutencao aberta na Educacao.');
}

$veiculoSaudeResumo = null;
$veiculoEducacaoResumo = null;

foreach ($porVeiculo as $item) {
    if (($item['placa'] ?? '') === $placaSaude) {
        $veiculoSaudeResumo = $item;
    }

    if (($item['placa'] ?? '') === $placaEducacao) {
        $veiculoEducacaoResumo = $item;
    }
}

if (! is_array($veiculoSaudeResumo) || ($veiculoSaudeResumo['preventiva_status'] ?? '') !== 'vencida') {
    throw new RuntimeException('Painel executivo por veiculo deveria destacar preventiva vencida na ambulancia.');
}

if (! is_array($veiculoEducacaoResumo) || (int) ($veiculoEducacaoResumo['manutencoes_abertas'] ?? 0) < 1) {
    throw new RuntimeException('Painel executivo por veiculo deveria consolidar manutencao aberta no onibus escolar.');
}

$pdo->prepare('DELETE FROM abastecimentos WHERE veiculo_id IN (?, ?)')->execute([$veiculoSaudeId, $veiculoEducacaoId]);
$pdo->prepare('DELETE FROM manutencoes WHERE veiculo_id IN (?, ?)')->execute([$veiculoSaudeId, $veiculoEducacaoId]);
$pdo->prepare('DELETE FROM viagens WHERE veiculo_id IN (?, ?)')->execute([$veiculoSaudeId, $veiculoEducacaoId]);
$pdo->prepare('DELETE FROM motoristas WHERE id IN (?, ?)')->execute([$motoristaSaudeId, $motoristaEducacaoId]);
$pdo->prepare('DELETE FROM veiculos WHERE id IN (?, ?)')->execute([$veiculoSaudeId, $veiculoEducacaoId]);

echo "Relatorio executivo validado com sucesso.\n";
