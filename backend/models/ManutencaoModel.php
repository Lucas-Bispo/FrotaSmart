<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class ManutencaoModel
{
    public function getAll(): array
    {
        global $pdo;

        $stmt = $pdo->query(
            'SELECT m.*, v.placa, v.modelo, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id
             ORDER BY m.data_abertura DESC, m.id DESC'
        );

        return $this->enrichPreventiveRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?array
    {
        global $pdo;

        $stmt = $pdo->prepare(
            'SELECT m.*, v.placa, v.modelo, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id
             WHERE m.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        $items = $this->enrichPreventiveRows([$result]);

        return $items[0] ?? null;
    }

    public function getRecent(int $limit = 5): array
    {
        global $pdo;

        $limit = max(1, $limit);
        $stmt = $pdo->query(
            'SELECT m.*, v.placa, v.modelo, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id
             ORDER BY m.data_abertura DESC, m.id DESC
             LIMIT ' . $limit
        );

        return $this->enrichPreventiveRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(array $data): int
    {
        global $pdo;

        $preventiva = $this->normalizePreventivePayload($data);
        $stmt = $pdo->prepare(
            'INSERT INTO manutencoes (
                veiculo_id,
                data_abertura,
                data_conclusao,
                km_referencia,
                km_proxima_preventiva,
                data_proxima_preventiva,
                recorrencia_dias,
                recorrencia_km,
                data,
                tipo,
                status,
                fornecedor,
                parceiro_id,
                custo_estimado,
                custo_final,
                custo,
                descricao,
                observacoes
             ) VALUES (
                :veiculo_id,
                :data_abertura,
                :data_conclusao,
                :km_referencia,
                :km_proxima_preventiva,
                :data_proxima_preventiva,
                :recorrencia_dias,
                :recorrencia_km,
                :data_legada,
                :tipo,
                :status,
                :fornecedor,
                :parceiro_id,
                :custo_estimado,
                :custo_final,
                :custo_legado,
                :descricao,
                :observacoes
             )'
        );

        $stmt->execute([
            ':veiculo_id' => $data['veiculo_id'],
            ':data_abertura' => $data['data_abertura'],
            ':data_conclusao' => $data['data_conclusao'],
            ':km_referencia' => $preventiva['km_referencia'],
            ':km_proxima_preventiva' => $preventiva['km_proxima_preventiva'],
            ':data_proxima_preventiva' => $preventiva['data_proxima_preventiva'],
            ':recorrencia_dias' => $preventiva['recorrencia_dias'],
            ':recorrencia_km' => $preventiva['recorrencia_km'],
            ':data_legada' => $data['data_abertura'],
            ':tipo' => $data['tipo'],
            ':status' => $data['status'],
            ':fornecedor' => $data['fornecedor'],
            ':parceiro_id' => $data['parceiro_id'] ?? null,
            ':custo_estimado' => $data['custo_estimado'],
            ':custo_final' => $data['custo_final'],
            ':custo_legado' => $this->legacyCost($data),
            ':descricao' => $data['descricao'],
            ':observacoes' => $data['observacoes'],
        ]);

        $id = (int) $pdo->lastInsertId();
        $this->syncVeiculoStatus((int) $data['veiculo_id']);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        global $pdo;

        $preventiva = $this->normalizePreventivePayload($data);
        $stmt = $pdo->prepare(
            'UPDATE manutencoes
             SET veiculo_id = :veiculo_id,
                 data_abertura = :data_abertura,
                 data_conclusao = :data_conclusao,
                 km_referencia = :km_referencia,
                 km_proxima_preventiva = :km_proxima_preventiva,
                 data_proxima_preventiva = :data_proxima_preventiva,
                 recorrencia_dias = :recorrencia_dias,
                 recorrencia_km = :recorrencia_km,
                 data = :data_legada,
                 tipo = :tipo,
                 status = :status,
                 fornecedor = :fornecedor,
                 parceiro_id = :parceiro_id,
                 custo_estimado = :custo_estimado,
                 custo_final = :custo_final,
                 custo = :custo_legado,
                 descricao = :descricao,
                 observacoes = :observacoes
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':veiculo_id' => $data['veiculo_id'],
            ':data_abertura' => $data['data_abertura'],
            ':data_conclusao' => $data['data_conclusao'],
            ':km_referencia' => $preventiva['km_referencia'],
            ':km_proxima_preventiva' => $preventiva['km_proxima_preventiva'],
            ':data_proxima_preventiva' => $preventiva['data_proxima_preventiva'],
            ':recorrencia_dias' => $preventiva['recorrencia_dias'],
            ':recorrencia_km' => $preventiva['recorrencia_km'],
            ':data_legada' => $data['data_abertura'],
            ':tipo' => $data['tipo'],
            ':status' => $data['status'],
            ':fornecedor' => $data['fornecedor'],
            ':parceiro_id' => $data['parceiro_id'] ?? null,
            ':custo_estimado' => $data['custo_estimado'],
            ':custo_final' => $data['custo_final'],
            ':custo_legado' => $this->legacyCost($data),
            ':descricao' => $data['descricao'],
            ':observacoes' => $data['observacoes'],
        ]);

        $this->syncVeiculoStatus((int) $data['veiculo_id']);
    }

    public function countAbertas(): int
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM manutencoes WHERE status IN ('aberta', 'em_andamento')");

        return (int) $stmt->fetchColumn();
    }

    public function countByVeiculoOpen(int $veiculoId): int
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM manutencoes WHERE veiculo_id = :veiculo_id AND status IN ('aberta', 'em_andamento')");
        $stmt->execute([':veiculo_id' => $veiculoId]);

        return (int) $stmt->fetchColumn();
    }

    public function countPreventivasVencidas(int $days = 30, int $kmTolerance = 500): int
    {
        return count(array_filter(
            $this->getPreventiveAlerts($days, $kmTolerance),
            static fn (array $item): bool => ($item['preventiva_alerta_status'] ?? '') === 'vencida'
        ));
    }

    public function countPreventivasProximas(int $days = 30, int $kmTolerance = 500): int
    {
        return count(array_filter(
            $this->getPreventiveAlerts($days, $kmTolerance),
            static fn (array $item): bool => ($item['preventiva_alerta_status'] ?? '') === 'proxima'
        ));
    }

    public function getPreventiveAlerts(int $days = 30, int $kmTolerance = 500): array
    {
        global $pdo;

        $stmt = $pdo->query(
            "SELECT m.*, v.placa, v.modelo, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
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

    public function evaluatePreventiveRuleForVeiculo(
        int $veiculoId,
        ?\DateTimeImmutable $referenceDate = null,
        ?int $currentKm = null,
        int $days = 30,
        int $kmTolerance = 500
    ): ?array {
        global $pdo;

        $stmt = $pdo->prepare(
            "SELECT m.*, v.placa, v.modelo, p.nome_fantasia AS parceiro_nome, p.tipo AS parceiro_tipo
             FROM manutencoes m
             INNER JOIN veiculos v ON v.id = m.veiculo_id
             LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id
             WHERE m.veiculo_id = :veiculo_id
               AND m.tipo = 'preventiva'
               AND m.status <> 'cancelada'
             ORDER BY m.data_abertura DESC, m.id DESC
             LIMIT 1"
        );
        $stmt->execute([':veiculo_id' => $veiculoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $items = $this->enrichPreventiveRows([$row], $days, $kmTolerance, $referenceDate, $currentKm);

        return $items[0] ?? null;
    }

    public function syncVeiculoStatus(int $veiculoId): void
    {
        global $pdo;
        $status = $this->countByVeiculoOpen($veiculoId) > 0 ? 'manutencao' : 'ativo';
        $stmt = $pdo->prepare('UPDATE veiculos SET status = :status WHERE id = :id');
        $stmt->execute([
            ':status' => $status,
            ':id' => $veiculoId,
        ]);
    }

    private function legacyCost(array $data): float
    {
        $final = (float) ($data['custo_final'] ?? 0);
        $estimated = (float) ($data['custo_estimado'] ?? 0);

        return $final > 0 ? $final : $estimated;
    }

    private function normalizePreventivePayload(array $data): array
    {
        $tipo = (string) ($data['tipo'] ?? '');

        if ($tipo !== 'preventiva') {
            return [
                'km_referencia' => null,
                'km_proxima_preventiva' => null,
                'data_proxima_preventiva' => null,
                'recorrencia_dias' => null,
                'recorrencia_km' => null,
            ];
        }

        return [
            'km_referencia' => $this->normalizeNullableInt($data['km_referencia'] ?? null),
            'km_proxima_preventiva' => $this->normalizeNullableInt($data['km_proxima_preventiva'] ?? null),
            'data_proxima_preventiva' => $this->normalizeNullableDate($data['data_proxima_preventiva'] ?? null),
            'recorrencia_dias' => $this->normalizeNullableInt($data['recorrencia_dias'] ?? null),
            'recorrencia_km' => $this->normalizeNullableInt($data['recorrencia_km'] ?? null),
        ];
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (int) $value);
    }

    private function normalizeNullableDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = trim((string) $value);

        return $date === '' ? null : $date;
    }

    private function enrichPreventiveRows(
        array $rows,
        int $days = 30,
        int $kmTolerance = 500,
        ?\DateTimeImmutable $referenceDate = null,
        ?int $currentKm = null
    ): array
    {
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

    private function resolveCurrentKmMap(array $rows): array
    {
        global $pdo;

        $veiculoIds = array_values(array_unique(array_map(
            static fn (array $row): int => (int) ($row['veiculo_id'] ?? 0),
            $rows
        )));
        $veiculoIds = array_values(array_filter($veiculoIds, static fn (int $id): bool => $id > 0));

        if ($veiculoIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($veiculoIds), '?'));
        $stmt = $pdo->prepare(
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

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $baseDate);

        if (! $parsed instanceof \DateTimeImmutable) {
            return null;
        }

        return $parsed->modify('+' . $recorrenciaDias . ' days')->format('Y-m-d');
    }

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

    private function resolvePreventiveStatus(
        array $row,
        int $days,
        int $kmTolerance,
        ?\DateTimeImmutable $referenceDate = null
    ): string
    {
        if (($row['tipo'] ?? '') !== 'preventiva') {
            return 'nao_aplicavel';
        }

        $today = $referenceDate ?? new \DateTimeImmutable('today');
        $alertDate = $today->modify('+' . max(1, $days) . ' days');
        $nextDate = $row['data_proxima_calculada'] ?? null;
        $currentKm = (int) ($row['km_atual_veiculo'] ?? 0);
        $nextKm = $row['km_proxima_calculada'] ?? null;

        $dateStatus = 'sem_regra';
        if (is_string($nextDate) && $nextDate !== '') {
            $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $nextDate);
            if ($parsed instanceof \DateTimeImmutable) {
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
