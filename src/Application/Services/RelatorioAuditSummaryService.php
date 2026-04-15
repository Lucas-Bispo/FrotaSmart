<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioAuditSummaryService
{
    /**
     * @param list<array<string, mixed>> $rows
     * @return array<string, int>
     */
    public function summarize(array $rows): array
    {
        $actors = [];
        $exports = 0;
        $blocked = 0;
        $mutations = 0;

        foreach ($rows as $row) {
            $actor = trim((string) ($row['actor'] ?? ''));
            if ($actor !== '') {
                $actors[$actor] = true;
            }

            if (($row['action'] ?? '') === 'export' || ($row['event'] ?? '') === 'relatorio.exported') {
                $exports++;
            }

            if (($row['action'] ?? '') === 'blocked') {
                $blocked++;
            }

            if (in_array((string) ($row['action'] ?? ''), ['create', 'update', 'delete', 'archive', 'restore'], true)) {
                $mutations++;
            }
        }

        return [
            'eventos_total' => count($rows),
            'atores_unicos' => count($actors),
            'exportacoes' => $exports,
            'bloqueios' => $blocked,
            'mutacoes' => $mutations,
        ];
    }
}
