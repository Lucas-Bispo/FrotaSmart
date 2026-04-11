<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AbastecimentoModel.php';
require_once __DIR__ . '/ManutencaoModel.php';

final class RelatorioOperacionalModel
{
    public function getSecretarias(): array
    {
        global $pdo;

        $stmt = $pdo->query(
            "SELECT secretaria FROM (
                SELECT secretaria_lotada AS secretaria FROM veiculos WHERE secretaria_lotada IS NOT NULL AND secretaria_lotada <> ''
                UNION
                SELECT secretaria FROM motoristas WHERE secretaria IS NOT NULL AND secretaria <> ''
                UNION
                SELECT secretaria FROM viagens WHERE secretaria IS NOT NULL AND secretaria <> ''
            ) AS secretarias
            ORDER BY secretaria ASC"
        );

        return array_values(array_filter(array_map(
            static fn (array $row): string => (string) $row['secretaria'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        )));
    }

    public function getVeiculos(): array
    {
        global $pdo;

        $stmt = $pdo->query(
            'SELECT id, placa, modelo, secretaria_lotada, status, deleted_at
             FROM veiculos
             ORDER BY placa ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAbastecimentoReport(array $filters): array
    {
        $abastecimentoModel = new AbastecimentoModel();
        $rows = $abastecimentoModel->getAll(
            $this->normalizeOptionalInt($filters['veiculo_id'] ?? null),
            $this->normalizeOptionalString($filters['data_inicio'] ?? null),
            $this->normalizeOptionalString($filters['data_fim'] ?? null)
        );

        $secretaria = $this->normalizeOptionalString($filters['secretaria'] ?? null);
        $status = $this->normalizeOptionalString($filters['status'] ?? null);

        return array_values(array_filter($rows, static function (array $row) use ($secretaria, $status): bool {
            if ($secretaria !== null && (string) ($row['secretaria'] ?? '') !== $secretaria) {
                return false;
            }

            if ($status !== null && (string) ($row['anomalia_status'] ?? 'normal') !== $status) {
                return false;
            }

            return true;
        }));
    }

    public function getManutencaoReport(array $filters): array
    {
        global $pdo;

        $conditions = [];
        $params = [];

        if (($dataInicio = $this->normalizeOptionalString($filters['data_inicio'] ?? null)) !== null) {
            $conditions[] = 'm.data_abertura >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if (($dataFim = $this->normalizeOptionalString($filters['data_fim'] ?? null)) !== null) {
            $conditions[] = 'm.data_abertura <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        if (($secretaria = $this->normalizeOptionalString($filters['secretaria'] ?? null)) !== null) {
            $conditions[] = 'v.secretaria_lotada = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        if (($veiculoId = $this->normalizeOptionalInt($filters['veiculo_id'] ?? null)) !== null) {
            $conditions[] = 'm.veiculo_id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if (($status = $this->normalizeOptionalString($filters['status'] ?? null)) !== null) {
            $conditions[] = 'm.status = :status';
            $params[':status'] = $status;
        }

        $sql = 'SELECT
                    m.*,
                    v.placa,
                    v.modelo,
                    v.secretaria_lotada,
                    p.nome_fantasia AS parceiro_nome
                FROM manutencoes m
                INNER JOIN veiculos v ON v.id = m.veiculo_id
                LEFT JOIN parceiros_operacionais p ON p.id = m.parceiro_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY m.data_abertura DESC, m.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getViagemReport(array $filters): array
    {
        global $pdo;

        $conditions = [];
        $params = [];

        if (($dataInicio = $this->normalizeOptionalString($filters['data_inicio'] ?? null)) !== null) {
            $conditions[] = 'DATE(v.data_saida) >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if (($dataFim = $this->normalizeOptionalString($filters['data_fim'] ?? null)) !== null) {
            $conditions[] = 'DATE(v.data_saida) <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        if (($secretaria = $this->normalizeOptionalString($filters['secretaria'] ?? null)) !== null) {
            $conditions[] = 'v.secretaria = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        if (($veiculoId = $this->normalizeOptionalInt($filters['veiculo_id'] ?? null)) !== null) {
            $conditions[] = 'v.veiculo_id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if (($status = $this->normalizeOptionalString($filters['status'] ?? null)) !== null) {
            $conditions[] = 'v.status = :status';
            $params[':status'] = $status;
        }

        $sql = 'SELECT
                    v.*,
                    ve.placa,
                    ve.modelo,
                    m.nome AS motorista_nome
                FROM viagens v
                INNER JOIN veiculos ve ON ve.id = v.veiculo_id
                INNER JOIN motoristas m ON m.id = v.motorista_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY v.data_saida DESC, v.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $kmSaida = (int) ($row['km_saida'] ?? 0);
            $kmChegada = isset($row['km_chegada']) ? (int) $row['km_chegada'] : null;
            $row['km_percorrido'] = ($kmChegada !== null && $kmChegada >= $kmSaida) ? $kmChegada - $kmSaida : null;
        }
        unset($row);

        return $rows;
    }

    public function getDisponibilidadeReport(array $filters): array
    {
        global $pdo;

        $conditions = [];
        $params = [];

        if (($secretaria = $this->normalizeOptionalString($filters['secretaria'] ?? null)) !== null) {
            $conditions[] = 'v.secretaria_lotada = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        if (($veiculoId = $this->normalizeOptionalInt($filters['veiculo_id'] ?? null)) !== null) {
            $conditions[] = 'v.id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if (($status = $this->normalizeOptionalString($filters['status'] ?? null)) !== null) {
            $conditions[] = 'v.status = :status';
            $params[':status'] = $status;
        }

        $sql = 'SELECT
                    v.id,
                    v.placa,
                    v.modelo,
                    v.secretaria_lotada,
                    v.status,
                    v.deleted_at,
                    v.quilometragem_inicial,
                    (
                        SELECT COUNT(*)
                        FROM viagens vi
                        WHERE vi.veiculo_id = v.id
                    ) AS total_viagens,
                    (
                        SELECT COUNT(*)
                        FROM manutencoes m
                        WHERE m.veiculo_id = v.id
                    ) AS total_manutencoes,
                    (
                        SELECT MAX(a.data_abastecimento)
                        FROM abastecimentos a
                        WHERE a.veiculo_id = v.id
                    ) AS ultimo_abastecimento,
                    (
                        SELECT MAX(DATE(vi2.data_saida))
                        FROM viagens vi2
                        WHERE vi2.veiculo_id = v.id
                    ) AS ultima_viagem
                FROM veiculos v';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY v.secretaria_lotada ASC, v.placa ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['situacao_disponibilidade'] = ! empty($row['deleted_at'])
                ? 'arquivado'
                : ((string) $row['status'] === 'manutencao' ? 'indisponivel_manutencao' : 'disponivel_operacao');
        }
        unset($row);

        return $rows;
    }

    public function getResumo(array $filters): array
    {
        $abastecimentos = $this->getAbastecimentoReport($filters);
        $manutencoes = $this->getManutencaoReport($filters);
        $viagens = $this->getViagemReport($filters);
        $disponibilidade = $this->getDisponibilidadeReport($filters);

        return [
            'abastecimentos' => count($abastecimentos),
            'gasto_abastecimento' => round(array_sum(array_map(static fn (array $row): float => (float) ($row['valor_total'] ?? 0), $abastecimentos)), 2),
            'manutencoes' => count($manutencoes),
            'custo_manutencao' => round(array_sum(array_map(static fn (array $row): float => (float) (($row['custo_final'] ?? 0) > 0 ? $row['custo_final'] : ($row['custo_estimado'] ?? 0)), $manutencoes)), 2),
            'viagens' => count($viagens),
            'km_viagens' => array_sum(array_map(static fn (array $row): int => (int) ($row['km_percorrido'] ?? 0), $viagens)),
            'veiculos_disponiveis' => count(array_filter($disponibilidade, static fn (array $row): bool => ($row['situacao_disponibilidade'] ?? '') === 'disponivel_operacao')),
        ];
    }

    public function getExecutiveSummaryBySecretaria(?string $dataInicio = null, ?string $dataFim = null): array
    {
        global $pdo;

        $summaries = [];

        foreach ($this->getSecretarias() as $secretaria) {
            $normalized = $this->normalizeSecretariaName($secretaria);
            $summaries[$normalized] = $this->emptySecretariaSummary($normalized);
        }

        $stmt = $pdo->query(
            "SELECT
                COALESCE(NULLIF(secretaria_lotada, ''), 'Secretaria nao informada') AS secretaria,
                SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS frota_ativa,
                SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS frota_arquivada,
                SUM(CASE WHEN deleted_at IS NULL AND status IN ('ativo', 'disponivel', 'em_viagem', 'reservado') THEN 1 ELSE 0 END) AS frota_operacao,
                SUM(CASE WHEN deleted_at IS NULL AND status IN ('manutencao', 'em_manutencao') THEN 1 ELSE 0 END) AS frota_manutencao
             FROM veiculos
             GROUP BY COALESCE(NULLIF(secretaria_lotada, ''), 'Secretaria nao informada')"
        );

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['frota_ativa'] = (int) ($row['frota_ativa'] ?? 0);
            $summaries[$secretaria]['frota_arquivada'] = (int) ($row['frota_arquivada'] ?? 0);
            $summaries[$secretaria]['frota_operacao'] = (int) ($row['frota_operacao'] ?? 0);
            $summaries[$secretaria]['frota_manutencao'] = (int) ($row['frota_manutencao'] ?? 0);
        }

        $stmt = $pdo->query(
            "SELECT
                COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada') AS secretaria,
                SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) AS motoristas_ativos
             FROM motoristas
             GROUP BY COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada')"
        );

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['motoristas_ativos'] = (int) ($row['motoristas_ativos'] ?? 0);
        }

        $conditions = [];
        $params = [];

        if ($dataInicio !== null && $dataInicio !== '') {
            $conditions[] = 'DATE(data_saida) >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== null && $dataFim !== '') {
            $conditions[] = 'DATE(data_saida) <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        $sqlViagens = "SELECT
                COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada') AS secretaria,
                COUNT(*) AS viagens_periodo,
                SUM(
                    CASE
                        WHEN km_chegada IS NOT NULL AND km_chegada >= km_saida THEN km_chegada - km_saida
                        ELSE 0
                    END
                ) AS km_viagens_periodo
             FROM viagens";

        if ($conditions !== []) {
            $sqlViagens .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sqlViagens .= " GROUP BY COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada')";

        $stmt = $pdo->prepare($sqlViagens);
        $stmt->execute($params);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['viagens_periodo'] = (int) ($row['viagens_periodo'] ?? 0);
            $summaries[$secretaria]['km_viagens_periodo'] = (int) ($row['km_viagens_periodo'] ?? 0);
        }

        $abastecimentoModel = new AbastecimentoModel();
        foreach ($abastecimentoModel->getAll(null, $dataInicio, $dataFim) as $row) {
            $secretaria = $this->normalizeSecretariaName(
                $row['veiculo_secretaria_lotada'] ?? $row['secretaria'] ?? null
            );
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);
            $summaries[$secretaria]['abastecimentos_periodo']++;
            $summaries[$secretaria]['gasto_abastecimento_periodo'] += (float) ($row['valor_total'] ?? 0);

            if (($row['anomalia_status'] ?? 'normal') !== 'normal') {
                $summaries[$secretaria]['alertas_abastecimento']++;
            }
        }

        $manutencaoModel = new ManutencaoModel();
        foreach ($manutencaoModel->getAll() as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria_lotada'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);

            if (in_array((string) ($row['status'] ?? ''), ['aberta', 'em_andamento'], true)) {
                $summaries[$secretaria]['manutencoes_abertas']++;
            }

            if ($this->isWithinDateRange((string) ($row['data_abertura'] ?? ''), $dataInicio, $dataFim)) {
                $summaries[$secretaria]['manutencoes_periodo']++;
                $summaries[$secretaria]['custo_manutencao_periodo'] += $this->resolveMaintenanceCost($row);
            }
        }

        foreach ($manutencaoModel->getPreventiveAlerts() as $row) {
            $secretaria = $this->normalizeSecretariaName($row['secretaria_lotada'] ?? null);
            $summaries[$secretaria] ??= $this->emptySecretariaSummary($secretaria);

            if (($row['preventiva_alerta_status'] ?? '') === 'vencida') {
                $summaries[$secretaria]['preventivas_vencidas']++;
            }

            if (($row['preventiva_alerta_status'] ?? '') === 'proxima') {
                $summaries[$secretaria]['preventivas_proximas']++;
            }
        }

        foreach ($summaries as &$summary) {
            $summary['gasto_abastecimento_periodo'] = round((float) $summary['gasto_abastecimento_periodo'], 2);
            $summary['custo_manutencao_periodo'] = round((float) $summary['custo_manutencao_periodo'], 2);
            $summary['custo_total_periodo'] = round(
                (float) $summary['gasto_abastecimento_periodo'] + (float) $summary['custo_manutencao_periodo'],
                2
            );
            $summary['disponibilidade_percentual'] = $summary['frota_ativa'] > 0
                ? round(($summary['frota_operacao'] / $summary['frota_ativa']) * 100, 1)
                : null;
            $summary['alertas_total'] = $summary['preventivas_vencidas']
                + $summary['preventivas_proximas']
                + $summary['manutencoes_abertas']
                + $summary['alertas_abastecimento'];
        }
        unset($summary);

        usort($summaries, static function (array $a, array $b): int {
            return [$b['custo_total_periodo'], $b['frota_ativa'], $a['secretaria']]
                <=> [$a['custo_total_periodo'], $a['frota_ativa'], $b['secretaria']];
        });

        return array_values($summaries);
    }

    public function getExecutiveSummaryByVeiculo(?string $dataInicio = null, ?string $dataFim = null, int $limit = 8): array
    {
        global $pdo;

        $summaries = [];

        foreach ($this->getVeiculos() as $veiculo) {
            $veiculoId = (int) ($veiculo['id'] ?? 0);

            if ($veiculoId <= 0) {
                continue;
            }

            $summaries[$veiculoId] = [
                'veiculo_id' => $veiculoId,
                'placa' => (string) ($veiculo['placa'] ?? ''),
                'modelo' => (string) ($veiculo['modelo'] ?? ''),
                'secretaria_lotada' => $this->normalizeSecretariaName($veiculo['secretaria_lotada'] ?? null),
                'status' => (string) ($veiculo['status'] ?? ''),
                'deleted_at' => $veiculo['deleted_at'] ?? null,
                'viagens_periodo' => 0,
                'km_viagens_periodo' => 0,
                'abastecimentos_periodo' => 0,
                'gasto_abastecimento_periodo' => 0.0,
                'alertas_abastecimento' => 0,
                'consumo_medio_km_l' => null,
                'manutencoes_abertas' => 0,
                'manutencoes_periodo' => 0,
                'custo_manutencao_periodo' => 0.0,
                'preventiva_status' => 'sem_plano',
                'preventiva_resumo' => 'Sem alerta preventivo no momento.',
                'total_alertas' => 0,
                'custo_total_periodo' => 0.0,
            ];
        }

        $conditions = [];
        $params = [];

        if ($dataInicio !== null && $dataInicio !== '') {
            $conditions[] = 'DATE(data_saida) >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim !== null && $dataFim !== '') {
            $conditions[] = 'DATE(data_saida) <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        $sqlViagens = 'SELECT veiculo_id, COUNT(*) AS viagens_periodo, SUM(
                CASE
                    WHEN km_chegada IS NOT NULL AND km_chegada >= km_saida THEN km_chegada - km_saida
                    ELSE 0
                END
            ) AS km_viagens_periodo
            FROM viagens';

        if ($conditions !== []) {
            $sqlViagens .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sqlViagens .= ' GROUP BY veiculo_id';

        $stmt = $pdo->prepare($sqlViagens);
        $stmt->execute($params);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            $summaries[$veiculoId]['viagens_periodo'] = (int) ($row['viagens_periodo'] ?? 0);
            $summaries[$veiculoId]['km_viagens_periodo'] = (int) ($row['km_viagens_periodo'] ?? 0);
        }

        $abastecimentoModel = new AbastecimentoModel();
        foreach ($abastecimentoModel->getAll(null, $dataInicio, $dataFim) as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            $summaries[$veiculoId]['abastecimentos_periodo']++;
            $summaries[$veiculoId]['gasto_abastecimento_periodo'] += (float) ($row['valor_total'] ?? 0);

            if (($row['anomalia_status'] ?? 'normal') !== 'normal') {
                $summaries[$veiculoId]['alertas_abastecimento']++;
            }

            if (($row['consumo_km_l'] ?? null) !== null) {
                $summaries[$veiculoId]['_consumo_soma'] = ($summaries[$veiculoId]['_consumo_soma'] ?? 0.0) + (float) $row['consumo_km_l'];
                $summaries[$veiculoId]['_consumo_leituras'] = ($summaries[$veiculoId]['_consumo_leituras'] ?? 0) + 1;
            }
        }

        $manutencaoModel = new ManutencaoModel();
        foreach ($manutencaoModel->getAll() as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            if (in_array((string) ($row['status'] ?? ''), ['aberta', 'em_andamento'], true)) {
                $summaries[$veiculoId]['manutencoes_abertas']++;
            }

            if ($this->isWithinDateRange((string) ($row['data_abertura'] ?? ''), $dataInicio, $dataFim)) {
                $summaries[$veiculoId]['manutencoes_periodo']++;
                $summaries[$veiculoId]['custo_manutencao_periodo'] += $this->resolveMaintenanceCost($row);
            }
        }

        foreach ($manutencaoModel->getPreventiveAlerts() as $row) {
            $veiculoId = (int) ($row['veiculo_id'] ?? 0);
            if (! isset($summaries[$veiculoId])) {
                continue;
            }

            $novoStatus = (string) ($row['preventiva_alerta_status'] ?? 'sem_plano');
            $statusAtual = (string) ($summaries[$veiculoId]['preventiva_status'] ?? 'sem_plano');

            if ($this->preventiveStatusSeverity($novoStatus) > $this->preventiveStatusSeverity($statusAtual)) {
                $summaries[$veiculoId]['preventiva_status'] = $novoStatus;
                $summaries[$veiculoId]['preventiva_resumo'] = (string) ($row['preventiva_alerta_resumo'] ?? 'Alerta preventivo identificado.');
            }
        }

        foreach ($summaries as &$summary) {
            $leituras = (int) ($summary['_consumo_leituras'] ?? 0);
            $soma = (float) ($summary['_consumo_soma'] ?? 0);
            $summary['consumo_medio_km_l'] = $leituras > 0 ? round($soma / $leituras, 2) : null;
            $summary['gasto_abastecimento_periodo'] = round((float) $summary['gasto_abastecimento_periodo'], 2);
            $summary['custo_manutencao_periodo'] = round((float) $summary['custo_manutencao_periodo'], 2);
            $summary['custo_total_periodo'] = round(
                (float) $summary['gasto_abastecimento_periodo'] + (float) $summary['custo_manutencao_periodo'],
                2
            );
            $summary['total_alertas'] = $summary['alertas_abastecimento'] + $summary['manutencoes_abertas'];

            if (in_array((string) $summary['preventiva_status'], ['vencida', 'proxima'], true)) {
                $summary['total_alertas']++;
            }

            if (! empty($summary['deleted_at'])) {
                $summary['total_alertas']++;
            }

            unset($summary['_consumo_soma'], $summary['_consumo_leituras']);
        }
        unset($summary);

        usort($summaries, static function (array $a, array $b): int {
            return [
                $b['total_alertas'],
                $b['custo_total_periodo'],
                $b['km_viagens_periodo'],
                $a['placa'],
            ] <=> [
                $a['total_alertas'],
                $a['custo_total_periodo'],
                $a['km_viagens_periodo'],
                $b['placa'],
            ];
        });

        return array_slice(array_values($summaries), 0, max(1, $limit));
    }

    public function exportCsv(string $report, array $filters): string
    {
        $rows = match ($report) {
            'abastecimentos' => $this->getAbastecimentoReport($filters),
            'manutencoes' => $this->getManutencaoReport($filters),
            'viagens' => $this->getViagemReport($filters),
            'disponibilidade' => $this->getDisponibilidadeReport($filters),
            default => [],
        };

        $stream = fopen('php://temp', 'r+');

        if ($rows === []) {
            fputcsv($stream, ['sem_dados']);
        } else {
            fputcsv($stream, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $content;
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private function normalizeSecretariaName(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : 'Secretaria nao informada';
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySecretariaSummary(string $secretaria): array
    {
        return [
            'secretaria' => $secretaria,
            'frota_ativa' => 0,
            'frota_arquivada' => 0,
            'frota_operacao' => 0,
            'frota_manutencao' => 0,
            'motoristas_ativos' => 0,
            'viagens_periodo' => 0,
            'km_viagens_periodo' => 0,
            'abastecimentos_periodo' => 0,
            'gasto_abastecimento_periodo' => 0.0,
            'alertas_abastecimento' => 0,
            'manutencoes_periodo' => 0,
            'manutencoes_abertas' => 0,
            'custo_manutencao_periodo' => 0.0,
            'preventivas_vencidas' => 0,
            'preventivas_proximas' => 0,
            'custo_total_periodo' => 0.0,
            'disponibilidade_percentual' => null,
            'alertas_total' => 0,
        ];
    }

    private function isWithinDateRange(string $date, ?string $dataInicio, ?string $dataFim): bool
    {
        if ($date === '') {
            return false;
        }

        if ($dataInicio !== null && $dataInicio !== '' && $date < $dataInicio) {
            return false;
        }

        if ($dataFim !== null && $dataFim !== '' && $date > $dataFim) {
            return false;
        }

        return true;
    }

    private function resolveMaintenanceCost(array $row): float
    {
        $final = (float) ($row['custo_final'] ?? 0);
        $estimado = (float) ($row['custo_estimado'] ?? 0);

        return $final > 0 ? $final : $estimado;
    }

    private function preventiveStatusSeverity(string $status): int
    {
        return match ($status) {
            'vencida' => 3,
            'proxima' => 2,
            'em_dia' => 1,
            default => 0,
        };
    }
}
