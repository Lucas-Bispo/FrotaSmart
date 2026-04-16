<?php

declare(strict_types=1);

namespace FrotaSmart\Infrastructure\ReadModels;

use FrotaSmart\Application\Contracts\AuditReportReadModelInterface;
use PDO;
use Throwable;

final class RelatorioOperacionalQueryService implements AuditReportReadModelInterface
{
    private \FrotaSmart\Application\Services\RelatorioQueryCriteriaService $criteria;

    public function __construct(
        private readonly PDO $connection
    ) {
        $this->criteria = new \FrotaSmart\Application\Services\RelatorioQueryCriteriaService();
    }

    /**
     * @return list<string>
     */
    public function fetchSecretarias(): array
    {
        $stmt = $this->connection->query(
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
            static fn (array $row): string => (string) ($row['secretaria'] ?? ''),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        )));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchVeiculos(): array
    {
        $stmt = $this->connection->query(
            'SELECT id, placa, modelo, secretaria_lotada, status, deleted_at
             FROM veiculos
             ORDER BY placa ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchManutencaoReport(array $filters): array
    {
        $criteria = $this->criteria->forOperationalReport($filters);
        $conditions = [];
        $params = [];

        if (($dataInicio = $criteria['data_inicio']) !== null) {
            $conditions[] = 'm.data_abertura >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if (($dataFim = $criteria['data_fim']) !== null) {
            $conditions[] = 'm.data_abertura <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        if (($secretaria = $criteria['secretaria']) !== null) {
            $conditions[] = 'v.secretaria_lotada = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        if (($veiculoId = $criteria['veiculo_id']) !== null) {
            $conditions[] = 'm.veiculo_id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if (($status = $criteria['status']) !== null) {
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

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchViagemReport(array $filters): array
    {
        $criteria = $this->criteria->forOperationalReport($filters);
        $conditions = [];
        $params = [];

        if (($dataInicio = $criteria['data_inicio']) !== null) {
            $conditions[] = 'DATE(v.data_saida) >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if (($dataFim = $criteria['data_fim']) !== null) {
            $conditions[] = 'DATE(v.data_saida) <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        if (($secretaria = $criteria['secretaria']) !== null) {
            $conditions[] = 'v.secretaria = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        if (($veiculoId = $criteria['veiculo_id']) !== null) {
            $conditions[] = 'v.veiculo_id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if (($status = $criteria['status']) !== null) {
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

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchDisponibilidadeReport(array $filters): array
    {
        $criteria = $this->criteria->forOperationalReport($filters);
        $conditions = [];
        $params = [];

        if (($secretaria = $criteria['secretaria']) !== null) {
            $conditions[] = 'v.secretaria_lotada = :secretaria';
            $params[':secretaria'] = $secretaria;
        }

        if (($veiculoId = $criteria['veiculo_id']) !== null) {
            $conditions[] = 'v.id = :veiculo_id';
            $params[':veiculo_id'] = $veiculoId;
        }

        if (($status = $criteria['status']) !== null) {
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

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchFleetSummaryBySecretaria(): array
    {
        $stmt = $this->connection->query(
            "SELECT
                COALESCE(NULLIF(secretaria_lotada, ''), 'Secretaria nao informada') AS secretaria,
                SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS frota_ativa,
                SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS frota_arquivada,
                SUM(CASE WHEN deleted_at IS NULL AND status IN ('ativo', 'disponivel', 'em_viagem', 'reservado') THEN 1 ELSE 0 END) AS frota_operacao,
                SUM(CASE WHEN deleted_at IS NULL AND status IN ('manutencao', 'em_manutencao') THEN 1 ELSE 0 END) AS frota_manutencao
             FROM veiculos
             GROUP BY COALESCE(NULLIF(secretaria_lotada, ''), 'Secretaria nao informada')"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchMotoristasAtivosBySecretaria(): array
    {
        $stmt = $this->connection->query(
            "SELECT
                COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada') AS secretaria,
                SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) AS motoristas_ativos
             FROM motoristas
             GROUP BY COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada')"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchViagensSummaryBySecretaria(?string $dataInicio = null, ?string $dataFim = null): array
    {
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

        $sql = "SELECT
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
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY COALESCE(NULLIF(secretaria, ''), 'Secretaria nao informada')";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchViagensSummaryByVeiculo(?string $dataInicio = null, ?string $dataFim = null): array
    {
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

        $sql = 'SELECT veiculo_id, COUNT(*) AS viagens_periodo, SUM(
                CASE
                    WHEN km_chegada IS NOT NULL AND km_chegada >= km_saida THEN km_chegada - km_saida
                    ELSE 0
                END
            ) AS km_viagens_periodo
            FROM viagens';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY veiculo_id';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchAuditRows(array $filters): array
    {
        $criteria = $this->criteria->forAuditReport($filters);
        $conditions = [];
        $params = [];

        if (($dataInicio = $criteria['data_inicio']) !== null) {
            $conditions[] = 'DATE(occurred_at) >= :data_inicio';
            $params[':data_inicio'] = $dataInicio;
        }

        if (($dataFim = $criteria['data_fim']) !== null) {
            $conditions[] = 'DATE(occurred_at) <= :data_fim';
            $params[':data_fim'] = $dataFim;
        }

        if (($actor = $criteria['ator']) !== null) {
            $conditions[] = 'actor LIKE :actor';
            $params[':actor'] = '%' . $actor . '%';
        }

        if (($event = $criteria['evento']) !== null) {
            $conditions[] = 'event LIKE :event';
            $params[':event'] = '%' . $event . '%';
        }

        if (($action = $criteria['status']) !== null) {
            $conditions[] = 'action = :action';
            $params[':action'] = $action;
        }

        if (($targetType = $criteria['tipo_alvo']) !== null) {
            $conditions[] = 'target_type = :target_type';
            $params[':target_type'] = $targetType;
        }

        $sql = 'SELECT
                    id,
                    event,
                    action,
                    target_type,
                    target_id,
                    actor,
                    actor_role,
                    ip,
                    occurred_at,
                    context_json
                FROM audit_logs';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY occurred_at DESC, id DESC LIMIT 500';

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<string>
     */
    public function fetchAuditTargetTypes(): array
    {
        try {
            $stmt = $this->connection->query(
                "SELECT DISTINCT target_type
                 FROM audit_logs
                 WHERE target_type IS NOT NULL AND target_type <> ''
                 ORDER BY target_type ASC"
            );

            return array_values(array_filter(array_map(
                static fn (array $row): string => (string) ($row['target_type'] ?? ''),
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            )));
        } catch (Throwable) {
            return [];
        }
    }

}
