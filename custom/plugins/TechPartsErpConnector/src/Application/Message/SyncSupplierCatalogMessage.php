<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Message;

/**
 * A bounded supplier catalog batch. For large imports, persist the payload and
 * enqueue only a durable batch identifier instead of serializing all rows.
 */
final readonly class SyncSupplierCatalogMessage
{
    /** @param list<array<string, mixed>> $rows */
    public function __construct(
        public string $jobId,
        public string $source,
        public array $rows,
        public int $batchSize = 100,
    ) {
        if (trim($jobId) === '' || trim($source) === '') {
            throw new \InvalidArgumentException('jobId and source are required');
        }
        if ($rows === []) {
            throw new \InvalidArgumentException('At least one supplier row is required');
        }
        if ($batchSize < 1 || $batchSize > 1000) {
            throw new \InvalidArgumentException('batchSize must be between 1 and 1000');
        }
    }
}
