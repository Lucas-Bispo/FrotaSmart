<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AbastecimentoModel.php';

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
}
