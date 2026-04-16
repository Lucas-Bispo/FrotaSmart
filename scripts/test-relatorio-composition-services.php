<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$summaryService = new \FrotaSmart\Application\Services\RelatorioOperationalSummaryService();
$selectorService = new \FrotaSmart\Application\Services\RelatorioDatasetSelectorService();

$summary = $summaryService->summarize(
    [
        ['valor_total' => 100.50],
        ['valor_total' => 49.50],
    ],
    [
        ['custo_final' => 0, 'custo_estimado' => 80],
        ['custo_final' => 20, 'custo_estimado' => 10],
    ],
    [
        ['km_percorrido' => 30],
        ['km_percorrido' => 45],
    ],
    [
        ['situacao_disponibilidade' => 'disponivel_operacao'],
        ['situacao_disponibilidade' => 'arquivado'],
        ['situacao_disponibilidade' => 'disponivel_operacao'],
    ]
);

if (($summary['gasto_abastecimento'] ?? 0.0) !== 150.0) {
    throw new RuntimeException('Resumo operacional deveria somar corretamente os gastos de abastecimento.');
}

if (($summary['custo_manutencao'] ?? 0.0) !== 100.0) {
    throw new RuntimeException('Resumo operacional deveria priorizar custo final e consolidar manutencoes.');
}

if (($summary['km_viagens'] ?? 0) !== 75 || ($summary['veiculos_disponiveis'] ?? 0) !== 2) {
    throw new RuntimeException('Resumo operacional deveria consolidar KM e disponibilidade.');
}

$selected = $selectorService->select('viagens', [
    'abastecimentos' => static fn (): array => [['tipo' => 'abastecimento']],
    'viagens' => static fn (): array => [['tipo' => 'viagem']],
]);

$unknown = $selectorService->select('inexistente', [
    'abastecimentos' => static fn (): array => [['tipo' => 'abastecimento']],
]);

if (($selected[0]['tipo'] ?? '') !== 'viagem') {
    throw new RuntimeException('Seletor de datasets deveria resolver apenas o provider solicitado.');
}

if ($unknown !== []) {
    throw new RuntimeException('Seletor de datasets deveria retornar array vazio para relatorio desconhecido.');
}

echo "Services de composicao de relatorio validados com sucesso.\n";
