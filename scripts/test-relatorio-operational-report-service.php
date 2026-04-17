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

    public function fetchDocumentacaoReport(array $filters): array
    {
        $this->lastFilters = $filters;

        return [
            [
                'veiculo_id' => 9,
                'placa' => 'ABC1D23',
                'modelo' => 'Sprinter',
                'secretaria_lotada' => 'Saude',
                'documentos_observacoes' => 'Apolice renovada.',
                'documento_tipo' => 'Licenciamento',
                'vencimento' => '2026-04-10',
                'situacao_documento' => 'vencido',
            ],
            [
                'veiculo_id' => 9,
                'placa' => 'ABC1D23',
                'modelo' => 'Sprinter',
                'secretaria_lotada' => 'Saude',
                'documentos_observacoes' => 'Apolice renovada.',
                'documento_tipo' => 'Seguro',
                'vencimento' => '2026-04-25',
                'situacao_documento' => 'vencendo',
            ],
        ];
    }

    public function fetchTransparenciaPublicaReport(array $filters): array
    {
        $this->lastFilters = $filters;

        return [[
            'veiculo_id' => 11,
            'placa' => 'XYZ9K88',
            'modelo' => 'Cronos',
            'tipo' => 'passeio',
            'combustivel' => 'flex',
            'secretaria_lotada' => 'Administracao',
            'status' => 'ativo',
            'abastecimentos_periodo' => 2,
            'gasto_abastecimento_periodo' => 320.5,
            'manutencoes_periodo' => 1,
            'custo_manutencao_periodo' => 90.0,
            'viagens_periodo' => 4,
            'km_viagens_periodo' => 180,
            'documentos_pendentes' => 1,
        ]];
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
$documentacao = $service->documentacao($filters);
$transparencia = $service->transparenciaPublica($filters);

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

if (($documentacao[0]['situacao_documental'] ?? '') !== 'vencido'
    || ($documentacao[0]['documentos_vencendo'] ?? null) !== 1) {
    throw new RuntimeException('Service operacional de relatorios deveria consolidar a documentacao por veiculo.');
}

if (($transparencia[0]['situacao_publicacao'] ?? '') !== 'pendencia_documental'
    || array_key_exists('motorista_nome', $transparencia[0])) {
    throw new RuntimeException('Service operacional de relatorios deveria classificar e preservar o dataset publico sem dados pessoais.');
}

echo "Service operacional de relatorios validado com sucesso.\n";
