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

    public function getDocumentacaoReport(array $filters): array
    {
        return [[
            'placa' => 'ABC1D23',
            'modelo' => 'Sprinter',
            'secretaria_lotada' => 'Saude',
            'situacao_documental' => 'vencido',
            'documentos_vencidos' => 1,
            'documentos_vencendo' => 1,
            'proximo_vencimento' => '2026-04-10',
            'documentos_monitorados' => 'Licenciamento: 2026-04-10 | Seguro: 2026-04-25',
            'pendencias_resumo' => 'Licenciamento vencido em 2026-04-10 | Seguro vence em 2026-04-25',
            'documentos_observacoes' => 'Apolice em renovacao.',
        ]];
    }

    public function getAuditReport(array $filters): array
    {
        return [];
    }

    public function getTransparenciaReport(array $filters): array
    {
        return [[
            'placa' => 'XYZ9K88',
            'modelo' => 'Cronos',
            'tipo' => 'passeio',
            'secretaria_lotada' => 'Administracao',
            'status' => 'ativo',
            'situacao_publicacao' => 'regular',
            'viagens_periodo' => 4,
            'km_viagens_periodo' => 180,
            'abastecimentos_periodo' => 2,
            'manutencoes_periodo' => 1,
            'gasto_abastecimento_periodo' => 320.5,
            'custo_manutencao_periodo' => 90.0,
            'documentos_pendentes' => 0,
        ]];
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

$documentPageData = relatorios_build_page_data(
    $fakeModel,
    'documentacao',
    [
        'data_inicio' => '',
        'data_fim' => '',
        'secretaria' => 'Saude',
        'veiculo_id' => '1',
        'status' => 'vencido',
        'ator' => '',
        'evento' => '',
        'tipo_alvo' => '',
    ],
    relatorios_report_labels()
);

if (($documentPageData['summaryCards'][2]['value'] ?? '') !== '1') {
    throw new RuntimeException('Helper de view deveria resumir documentos vencidos na aba documental.');
}

if (($documentPageData['reportTitle'] ?? '') !== 'Documentacao') {
    throw new RuntimeException('Helper de view deveria expor o titulo da aba documental.');
}

if (! str_contains((string) (($documentPageData['rowMarkupList'][0] ?? '')), 'Licenciamento vencido em 2026-04-10')) {
    throw new RuntimeException('Helper de view deveria renderizar o resumo de pendencias da aba documental.');
}

$transparencyPageData = relatorios_build_page_data(
    $fakeModel,
    'transparencia',
    [
        'data_inicio' => '',
        'data_fim' => '',
        'secretaria' => 'Administracao',
        'veiculo_id' => '',
        'status' => 'ativo',
        'ator' => '',
        'evento' => '',
        'tipo_alvo' => '',
    ],
    relatorios_report_labels()
);

if (($transparencyPageData['summaryCards'][0]['value'] ?? '') !== '1') {
    throw new RuntimeException('Helper de view deveria resumir a frota publicada na aba de transparencia.');
}

if (($transparencyPageData['reportTitle'] ?? '') !== 'Transparencia') {
    throw new RuntimeException('Helper de view deveria expor o titulo da aba de transparencia.');
}

if (! str_contains((string) (($transparencyPageData['rowMarkupList'][0] ?? '')), 'Regular para publicacao')) {
    throw new RuntimeException('Helper de view deveria renderizar a classificacao publica do dataset de transparencia.');
}

echo "Helpers de view de relatorios validados com sucesso.\n";
