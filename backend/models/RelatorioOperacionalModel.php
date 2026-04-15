<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

final class RelatorioOperacionalModel
{
    private PDO $connection;
    private \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService $queries;
    private \FrotaSmart\Infrastructure\ReadModels\AbastecimentoReadModel $abastecimentos;
    private \FrotaSmart\Infrastructure\ReadModels\ManutencaoReadModel $manutencoes;
    private \FrotaSmart\Application\Services\RelatorioExecutiveSummaryService $executiveSummaries;
    private \FrotaSmart\Application\Services\RelatorioAuditSummaryService $auditSummaries;
    private \FrotaSmart\Application\Services\RelatorioCsvExporterService $csvExporter;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? $this->resolveLegacyConnection();
        $this->queries = new \FrotaSmart\Infrastructure\ReadModels\RelatorioOperacionalQueryService($this->connection);
        $this->abastecimentos = new \FrotaSmart\Infrastructure\ReadModels\AbastecimentoReadModel($this->connection);
        $this->manutencoes = new \FrotaSmart\Infrastructure\ReadModels\ManutencaoReadModel($this->connection);
        $this->executiveSummaries = new \FrotaSmart\Application\Services\RelatorioExecutiveSummaryService(
            $this->queries,
            $this->abastecimentos,
            $this->manutencoes
        );
        $this->auditSummaries = new \FrotaSmart\Application\Services\RelatorioAuditSummaryService();
        $this->csvExporter = new \FrotaSmart\Application\Services\RelatorioCsvExporterService();
    }

    public function getSecretarias(): array
    {
        return $this->queries->fetchSecretarias();
    }

    public function getVeiculos(): array
    {
        return $this->queries->fetchVeiculos();
    }

    public function getAbastecimentoReport(array $filters): array
    {
        $rows = $this->abastecimentos->fetchAll(
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
        return $this->queries->fetchManutencaoReport($filters);
    }

    public function getViagemReport(array $filters): array
    {
        $rows = $this->queries->fetchViagemReport($filters);

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
        $rows = $this->queries->fetchDisponibilidadeReport($filters);

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
        return $this->executiveSummaries->buildBySecretaria($dataInicio, $dataFim);
    }

    public function getExecutiveSummaryByVeiculo(?string $dataInicio = null, ?string $dataFim = null, int $limit = 8): array
    {
        return $this->executiveSummaries->buildByVeiculo($dataInicio, $dataFim, $limit);
    }

    public function getAuditReport(array $filters): array
    {
        return $this->fetchAuditRows($filters);
    }

    public function getAuditSummary(array $filters): array
    {
        return $this->auditSummaries->summarize($this->fetchAuditRows($filters));
    }

    public function getAuditTargetTypes(): array
    {
        return $this->queries->fetchAuditTargetTypes();
    }

    public function exportCsv(string $report, array $filters): string
    {
        return $this->csvExporter->export($this->resolveReportRows($report, $filters));
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

    private function fetchAuditRows(array $filters): array
    {
        $rows = $this->queries->fetchAuditRows($filters);

        foreach ($rows as &$row) {
            $context = $this->decodeAuditContext($row['context_json'] ?? null);
            $row['context_summary'] = $this->summarizeAuditContext($context);
        }
        unset($row);

        return $rows;
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    private function resolveReportRows(string $report, array $filters): array
    {
        return match ($report) {
            'abastecimentos' => $this->getAbastecimentoReport($filters),
            'manutencoes' => $this->getManutencaoReport($filters),
            'viagens' => $this->getViagemReport($filters),
            'disponibilidade' => $this->getDisponibilidadeReport($filters),
            'auditoria' => $this->getAuditReport($filters),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAuditContext(mixed $json): array
    {
        if (! is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function summarizeAuditContext(array $context): string
    {
        if ($context === []) {
            return 'Sem contexto adicional.';
        }

        $parts = [];
        foreach ($context as $key => $value) {
            if (in_array($key, ['request_uri'], true)) {
                continue;
            }

            if (is_array($value)) {
                $rendered = implode('; ', array_map(static fn (mixed $item): string => (string) $item, $value));
            } elseif (is_bool($value)) {
                $rendered = $value ? 'sim' : 'nao';
            } elseif ($value === null) {
                continue;
            } else {
                $rendered = trim((string) $value);
            }

            if ($rendered === '') {
                continue;
            }

            $parts[] = str_replace('_', ' ', (string) $key) . ': ' . $rendered;

            if (count($parts) >= 4) {
                break;
            }
        }

        return $parts === [] ? 'Sem contexto adicional.' : implode(' | ', $parts);
    }

    private function resolveLegacyConnection(): PDO
    {
        global $pdo;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        throw new RuntimeException('Conexao PDO indisponivel para RelatorioOperacionalModel.');
    }
}
