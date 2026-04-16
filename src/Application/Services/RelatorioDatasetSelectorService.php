<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioDatasetSelectorService
{
    /**
     * @param array<string, callable(): list<array<string, mixed>>> $providers
     * @return list<array<string, mixed>>
     */
    public function select(string $report, array $providers): array
    {
        if (! isset($providers[$report])) {
            return [];
        }

        return $providers[$report]();
    }
}
