<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/models/RelatorioOperacionalModel.php';
require_once __DIR__ . '/../frontend/views/helpers/relatorios_view_helpers.php';

$fakeModel = new class {
    public function getSecretarias(): array
    {
        return ['Saude'];
    }

    public function getVeiculos(): array
    {
        return [['id' => 1, 'placa' => 'ABC1D23', 'modelo' => 'Sprinter']];
    }

    public function getResumo(array $filters): array
    {
        return [
            'gasto_abastecimento' => 120.5,
            'custo_manutencao' => 80.0,
            'viagens' => 2,
            'km_viagens' => 40,
            'veiculos_disponiveis' => 1,
        ];
    }

    public function getAuditSummary(array $filters): array
    {
        return [
            'eventos_total' => 3,
            'atores_unicos' => 2,
            'exportacoes' => 1,
            'bloqueios' => 0,
        ];
    }

    public function getAuditTargetTypes(): array
    {
        return ['veiculo'];
    }

    public function getAbastecimentoReport(array $filters): array
    {
        return [[
            'placa' => 'ABC1D23',
            'modelo' => 'Sprinter',
            'secretaria' => 'Saude',
            'data_abastecimento' => '2026-04-15',
            'tipo_combustivel' => 'diesel',
            'consumo_km_l' => 8.2,
            'anomalia_status' => 'normal',
            'valor_total' => 120.5,
            'litros' => 14.7,
        ]];
    }

    public function getManutencaoReport(array $filters): array
    {
        return [];
    }

    public function getViagemReport(array $filters): array
    {
        return [];
    }

    public function getDisponibilidadeReport(array $filters): array
    {
        return [];
    }

    public function getAuditReport(array $filters): array
    {
        return [];
    }
};

$pageData = relatorios_build_page_data(
    $fakeModel,
    'abastecimentos',
    [
        'data_inicio' => '',
        'data_fim' => '',
        'secretaria' => 'Saude',
        'veiculo_id' => '1',
        'status' => '',
        'ator' => '',
        'evento' => '',
        'tipo_alvo' => '',
    ],
    relatorios_report_labels()
);

if (count($pageData['rows'] ?? []) !== 1) {
    throw new RuntimeException('Helper de view deveria montar as linhas do relatorio ativo.');
}

if (($pageData['summaryCards'][0]['value'] ?? '') !== 'R$ 120,50') {
    throw new RuntimeException('Helper de view deveria montar cards com os valores resumidos do relatorio.');
}

if (! str_contains((string) ($pageData['filterFieldsMarkup'] ?? ''), 'Todos os veiculos')) {
    throw new RuntimeException('Helper de view deveria montar os campos de filtro operacionais.');
}

if (! str_contains((string) ($pageData['exportQuery'] ?? ''), 'relatorio=abastecimentos')) {
    throw new RuntimeException('Helper de view deveria preparar a query de exportacao da aba atual.');
}

if (($pageData['reportTitle'] ?? '') !== 'Abastecimentos') {
    throw new RuntimeException('Helper de view deveria expor o titulo formatado da aba atual.');
}

if (($pageData['clearHref'] ?? '') !== '/relatorios.php?relatorio=abastecimentos') {
    throw new RuntimeException('Helper de view deveria preparar o link de limpeza da aba atual.');
}

if (! str_contains((string) (($pageData['rowMarkupList'][0] ?? '')), 'ABC1D23')) {
    throw new RuntimeException('Helper de view deveria preparar o markup final das linhas da tabela.');
}

echo "Helpers de view de relatorios validados com sucesso.\n";
