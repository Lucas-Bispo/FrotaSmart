<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioOperationalSummaryService
{
    /**
     * @param list<array<string, mixed>> $abastecimentos
     * @param list<array<string, mixed>> $manutencoes
     * @param list<array<string, mixed>> $viagens
     * @param list<array<string, mixed>> $disponibilidade
     * @return array<string, int|float>
     */
    public function summarize(
        array $abastecimentos,
        array $manutencoes,
        array $viagens,
        array $disponibilidade
    ): array {
        return [
            'abastecimentos' => count($abastecimentos),
            'gasto_abastecimento' => round(array_sum(array_map(
                static fn (array $row): float => (float) ($row['valor_total'] ?? 0),
                $abastecimentos
            )), 2),
            'manutencoes' => count($manutencoes),
            'custo_manutencao' => round(array_sum(array_map(
                static fn (array $row): float => (float) (($row['custo_final'] ?? 0) > 0 ? $row['custo_final'] : ($row['custo_estimado'] ?? 0)),
                $manutencoes
            )), 2),
            'viagens' => count($viagens),
            'km_viagens' => array_sum(array_map(
                static fn (array $row): int => (int) ($row['km_percorrido'] ?? 0),
                $viagens
            )),
            'veiculos_disponiveis' => count(array_filter(
                $disponibilidade,
                static fn (array $row): bool => ($row['situacao_disponibilidade'] ?? '') === 'disponivel_operacao'
            )),
        ];
    }
}
