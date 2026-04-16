<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$readModel = new class () implements \FrotaSmart\Application\Contracts\AbastecimentoReportReadModelInterface {
    /** @var array<string, int|string|null> */
    public array $lastCriteria = [];

    public function fetchByCriteria(array $criteria): array
    {
        $this->lastCriteria = $criteria;

        return [
            ['id' => 1, 'placa' => 'AAA1A11', 'secretaria' => 'Saude', 'anomalia_status' => 'normal'],
            ['id' => 2, 'placa' => 'AAA1A11', 'secretaria' => 'Saude', 'anomalia_status' => 'critico'],
            ['id' => 3, 'placa' => 'BBB2B22', 'secretaria' => 'Educacao', 'anomalia_status' => 'atencao'],
        ];
    }
};

$service = new \FrotaSmart\Application\Services\RelatorioAbastecimentoReportService(
    $readModel,
    new \FrotaSmart\Application\Services\RelatorioAbastecimentoCriteriaService(),
    new \FrotaSmart\Application\Services\RelatorioAbastecimentoFilterService()
);

$saude = $service->generate([
    'secretaria' => 'Saude',
]);

$critico = $service->generate([
    'status' => 'critico',
]);

$veiculoPeriodo = $service->generate([
    'veiculo_id' => '1',
    'data_inicio' => '2026-04-02',
    'data_fim' => '2026-04-30',
]);

if (count($saude) !== 2) {
    throw new RuntimeException('Relatorio de abastecimento deveria filtrar registros pela secretaria normalizada.');
}

if (count($critico) !== 1 || ($critico[0]['placa'] ?? '') !== 'AAA1A11') {
    throw new RuntimeException('Relatorio de abastecimento deveria aplicar o filtro por anomalia apos enriquecer as linhas.');
}

if (count($veiculoPeriodo) !== 3) {
    throw new RuntimeException('Relatorio de abastecimento deveria preservar as linhas retornadas pelo read model quando nao houver filtro residual.');
}

if (($readModel->lastCriteria['veiculo_id'] ?? null) !== 1
    || ($readModel->lastCriteria['data_inicio'] ?? null) !== '2026-04-02'
    || ($readModel->lastCriteria['data_fim'] ?? null) !== '2026-04-30') {
    throw new RuntimeException('Relatorio de abastecimento deveria repassar criterios normalizados ao read model.');
}

echo "Service de relatorio de abastecimento validado com sucesso.\n";
