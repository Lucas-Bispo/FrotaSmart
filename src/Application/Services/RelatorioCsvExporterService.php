<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioCsvExporterService
{
    /**
     * @param list<array<string, mixed>> $rows
     */
    public function export(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

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
}
