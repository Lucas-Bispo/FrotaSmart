<?php

declare(strict_types=1);

namespace FrotaSmart\Application\Contracts;

interface AuditReportReadModelInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function fetchAuditRows(array $filters): array;

    /**
     * @return list<string>
     */
    public function fetchAuditTargetTypes(): array;
}
