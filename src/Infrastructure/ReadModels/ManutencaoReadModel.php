<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\ReadModels;

use DateTimeImmutable;
use PDO;

final class ManutencaoReadModel
{
    public function __construct(
        private readonly PDO $connection
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchAll(): array
    {
        $stmt = $this->connection->query(
            'SELECT
                m.*,
                v.placa,
                v.modelo,
                v.secretaria_lotada,
                p.nome_fantasia AS parceiro_nome,
                p.tipo AS parceiro_tipo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id
             ORDER BY m.data_abertura DESC, m.id DESC'
        );

        return $this->enrichPreventiveRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchPreventiveAlerts(int $days = 30, int $kmTolerance = 500): array
    {
        $stmt = $this->connection->query(
            "SELECT
                m.*,
                v.placa,
                v.modelo,
                v.secretaria_lotada,
                p.nome_fantasia AS parceiro_nome,
                p.tipo AS parceiro_tipo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id
             WHERE m.tipo = 'preventiva'
               AND m.status <> 'cancelada'
             ORDER BY m.data_abertura DESC, m.id DESC"
        );

        $items = $this->enrichPreventiveRows($stmt->fetchAll(PDO::FETCH_ASSOC), $days, $kmTolerance);

        return array_values(array_filter(
            $items,
            static fn (array $item): bool => in_array(($item['preventiva_alerta_status'] ?? ''), ['vencida', 'proxima'], true)
        ));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function enrichPreventiveRows(
        array $rows,
        int $days = 30,
        int $kmTolerance = 500,
        ?DateTimeImmutable $referenceDate = null,
        ?int $currentKm = null
    ): array {
        if ($rows === []) {
            return [];
        }

        $kmMap = $this->resolveCurrentKmMap($rows);
        $enriched = [];

        foreach ($rows as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            $row['km_atual_veiculo'] = $currentKm ?? ($kmMap[$veiculoId] ?? 0);
            $row['data_proxima_calculada'] = $this->resolveNextDate($row);
            $row['km_proxima_calculada'] = $this->resolveNextKm($row);
            $row['preventiva_alerta_status'] = $this->resolvePreventiveStatus($row, $days, $kmTolerance, $referenceDate);
            $row['preventiva_alerta_resumo'] = $this->buildPreventiveSummary($row);
            $enriched[] = $row;
        }

        return $enriched;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<int, int>
     */
    private function resolveCurrentKmMap(array $rows): array
    {
        $veiculoIds = array_values(array_unique(array_map(
            static fn (array $row): int => (int) ($row['veiculo_id'] ?? 0),
            $rows
        )));
        $veiculoIds = array_values(array_filter($veiculoIds, static fn (int $id): bool => $id > 0));

        if ($veiculoIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($veiculoIds), '?'));
        $stmt = $this->connection->prepare(
            'SELECT
                v.id,
                GREATEST(
                    COALESCE(v.quilometragem_inicial, 0),
                    COALESCE((
                        SELECT MAX(a.km_atual)
                        FROM abastecimentos a
                        WHERE a.veiculo_id = v.id
                    ), 0),
                    COALESCE((
                        SELECT MAX(COALESCE(vi.km_chegada, vi.km_saida))
                        FROM viagens vi
                        WHERE vi.veiculo_id = v.id
                    ), 0)
                ) AS km_atual
             FROM veiculos v
             WHERE v.id IN (' . $placeholders . ')'
        );
        $stmt->execute($veiculoIds);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $map[(int) $item['id']] = (int) $item['km_atual'];
        }

        return $map;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveNextDate(array $row): ?string
    {
        if (! empty($row['data_proxima_preventiva'])) {
            return (string) $row['data_proxima_preventiva'];
        }

        $recorrenciaDias = (int) ($row['recorrencia_dias'] ?? 0);
        $baseDate = (string) ($row['data_conclusao'] ?: $row['data_abertura'] ?? '');

        if ($recorrenciaDias <= 0 || $baseDate === '') {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $baseDate);

        if (! $parsed instanceof DateTimeImmutable) {
            return null;
        }

        return $parsed->modify('+' . $recorrenciaDias . ' days')->format('Y-m-d');
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolveNextKm(array $row): ?int
    {
        if (! empty($row['km_proxima_preventiva'])) {
            return (int) $row['km_proxima_preventiva'];
        }

        $recorrenciaKm = (int) ($row['recorrencia_km'] ?? 0);
        $kmReferencia = (int) ($row['km_referencia'] ?? 0);

        if ($recorrenciaKm <= 0 || $kmReferencia <= 0) {
            return null;
        }

        return $kmReferencia + $recorrenciaKm;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resolvePreventiveStatus(
        array $row,
        int $days,
        int $kmTolerance,
        ?DateTimeImmutable $referenceDate = null
    ): string {
        if (($row['tipo'] ?? '') !== 'preventiva') {
            return 'nao_aplicavel';
        }

        $today = $referenceDate ?? new DateTimeImmutable('today');
        $alertDate = $today->modify('+' . max(1, $days) . ' days');
        $nextDate = $row['data_proxima_calculada'] ?? null;
        $currentKm = (int) ($row['km_atual_veiculo'] ?? 0);
        $nextKm = $row['km_proxima_calculada'] ?? null;

        $dateStatus = 'sem_regra';
        if (is_string($nextDate) && $nextDate !== '') {
            $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $nextDate);
            if ($parsed instanceof DateTimeImmutable) {
                $dateStatus = $parsed < $today ? 'vencida' : ($parsed <= $alertDate ? 'proxima' : 'em_dia');
            }
        }

        $kmStatus = 'sem_regra';
        if (is_int($nextKm) && $nextKm > 0 && $currentKm > 0) {
            $kmStatus = $currentKm > $nextKm ? 'vencida' : ($currentKm >= ($nextKm - max(1, $kmTolerance)) ? 'proxima' : 'em_dia');
        }

        if (in_array('vencida', [$dateStatus, $kmStatus], true)) {
            return 'vencida';
        }

        if (in_array('proxima', [$dateStatus, $kmStatus], true)) {
            return 'proxima';
        }

        if ($dateStatus === 'sem_regra' && $kmStatus === 'sem_regra') {
            return 'sem_plano';
        }

        return 'em_dia';
    }

    /**
     * @param array<string, mixed> $row
     */
    private function buildPreventiveSummary(array $row): string
    {
        if (($row['tipo'] ?? '') !== 'preventiva') {
            return 'Sem plano preventivo.';
        }

        $parts = [];
        if (! empty($row['data_proxima_calculada'])) {
            $parts[] = 'Data prevista: ' . $row['data_proxima_calculada'];
        }
        if (! empty($row['km_proxima_calculada'])) {
            $parts[] = 'KM previsto: ' . number_format((float) $row['km_proxima_calculada'], 0, ',', '.');
        }
        if (! empty($row['recorrencia_dias'])) {
            $parts[] = 'Recorrencia: ' . (int) $row['recorrencia_dias'] . ' dias';
        }
        if (! empty($row['recorrencia_km'])) {
            $parts[] = 'Recorrencia: ' . number_format((float) $row['recorrencia_km'], 0, ',', '.') . ' km';
        }

        return $parts === [] ? 'Plano preventivo sem parametros futuros.' : implode(' | ', $parts);
    }
}
