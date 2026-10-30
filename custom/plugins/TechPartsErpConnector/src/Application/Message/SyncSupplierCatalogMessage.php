<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Message;

/**
 * Carries one bounded batch of supplier catalog rows to the asynchronous worker.
 *
 * Keep messages small; for large imports, store the batch in durable storage and
 * send only a batch identifier instead of serializing the full payload.
 */
final readonly class SyncSupplierCatalogMessage
{
    /**
     * @param list<array<string, mixed>> $rows
     */
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
