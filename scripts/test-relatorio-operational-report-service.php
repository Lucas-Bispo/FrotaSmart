<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$readModel = new class () implements \FrotaSmart\Application\Contracts\RelatorioOperationalReadModelInterface {
    /** @var array<string, mixed> */
    public array $lastFilters = [];

    public function fetchManutencaoReport(array $filters): array
    {
        $this->lastFilters = $filters;

        return [
            ['id' => 1, 'status' => 'aberta', 'custo_estimado' => 120.0],
        ];
    }

    public function fetchViagemReport(array $filters): array
    {
        $this->lastFilters = $filters;

        return [
            ['id' => 2, 'km_saida' => 100, 'km_chegada' => 155],
        ];
    }

    public function fetchDisponibilidadeReport(array $filters): array
    {
        $this->lastFilters = $filters;

        return [
            ['id' => 3, 'deleted_at' => null, 'status' => 'ativo'],
            ['id' => 4, 'deleted_at' => '2026-04-16 09:00:00', 'status' => 'ativo'],
        ];
    }
};

$service = new \FrotaSmart\Application\Services\RelatorioOperationalReportService(
    $readModel,
    new \FrotaSmart\Application\Services\RelatorioRowTransformerService()
);

$filters = [
    'data_inicio' => '2026-04-01',
    'veiculo_id' => '8',
];

$manutencoes = $service->manutencoes($filters);
$viagens = $service->viagens($filters);
$disponibilidade = $service->disponibilidade($filters);

if (($readModel->lastFilters['veiculo_id'] ?? null) !== '8') {
    throw new RuntimeException('Service operacional de relatorios deveria repassar os filtros ao read model.');
}

if (($manutencoes[0]['status'] ?? '') !== 'aberta') {
    throw new RuntimeException('Service operacional de relatorios deveria preservar as linhas brutas de manutencao.');
}

if (($viagens[0]['km_percorrido'] ?? null) !== 55) {
    throw new RuntimeException('Service operacional de relatorios deveria enriquecer viagens com KM percorrido.');
}

if (($disponibilidade[0]['situacao_disponibilidade'] ?? '') !== 'disponivel_operacao'
    || ($disponibilidade[1]['situacao_disponibilidade'] ?? '') !== 'arquivado') {
    throw new RuntimeException('Service operacional de relatorios deveria classificar disponibilidade antes de devolver as linhas.');
}

echo "Service operacional de relatorios validado com sucesso.\n";
