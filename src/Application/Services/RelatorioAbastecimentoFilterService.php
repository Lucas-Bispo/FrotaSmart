<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Services;

final class RelatorioAbastecimentoFilterService
{
    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function filter(array $rows, ?string $secretaria = null, ?string $status = null): array
    {
        return array_values(array_filter(
            $rows,
            static function (array $row) use ($secretaria, $status): bool {
                if ($secretaria !== null && (string) ($row['secretaria'] ?? '') !== $secretaria) {
                    return false;
                }

                if ($status !== null && (string) ($row['anomalia_status'] ?? 'normal') !== $status) {
                    return false;
                }

                return true;
            }
        ));
    }
}
