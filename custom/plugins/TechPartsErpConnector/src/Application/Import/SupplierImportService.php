<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use TechPartsErpConnector\Domain\Model\SupplierReference;

final readonly class SupplierImportService
{
    private const MAX_STORED_ERRORS = 1000;

    public function __construct(private EntityRepository $repository, private LoggerInterface $logger)
    {
    }

    /**
     * Imports iterable rows in bounded DAL batches. A failed batch throws to the caller for retry.
     * Invalid rows are rejected and logged without aborting the entire import. Error details are
     * capped so a malformed multi-million-row feed cannot exhaust memory just by accumulating errors.
     *
     * @param iterable<SupplierImportRow> $rows
     */
    public function import(iterable $rows, Context $context, int $batchSize = 100): SupplierImportResult
    {
        if ($batchSize < 1 || $batchSize > 1000) {
            throw new \InvalidArgumentException('batchSize must be between 1 and 1000');
        }

        $received = 0;
        $accepted = 0;
        $rejected = 0;
        $errors = [];
        $batch = [];

        foreach ($rows as $row) {
            ++$received;
            try {
                $reference = new SupplierReference($row->supplierCode, $row->externalProductId);
                if ($row->productId !== null && !Uuid::isValid($row->productId)) {
                    throw new \InvalidArgumentException('Invalid Shopware product UUID');
                }

                $batch[] = [
                    // Stable, namespaced ID makes re-imports idempotent. Keep this namespace/version stable.
                    'id' => substr(hash('sha256', "techparts:supplier-reference:v1\0" . $reference->key()), 0, 32),
                    'supplierCode' => $reference->supplierCode,
                    'externalProductId' => $reference->externalProductId,
                    'productId' => $row->productId,
                ];

                if (count($batch) >= $batchSize) {
                    $accepted += $this->upsertBatch($batch, $context);
                    $batch = [];
                }
            } catch (\InvalidArgumentException $exception) {
                ++$rejected;
                if (count($errors) < self::MAX_STORED_ERRORS) {
                    $errors[] = ['row' => $received, 'error' => $exception->getMessage()];
                }
                $this->logger->warning('Supplier import row rejected', [
                    'row' => $received,
                    'reason' => $exception->getMessage(),
                ]);
            }
        }

        if ($batch !== []) {
            $accepted += $this->upsertBatch($batch, $context);
        }

        $this->logger->info('Supplier import finished', [
            'received' => $received,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'storedErrorDetails' => count($errors),
        ]);

        return new SupplierImportResult($received, $accepted, $rejected, $errors);
    }

    /**
     * Keep DAL repository calls outside the row-iteration loop while retaining bounded batches.
     *
     * @param list<array{id: string, supplierCode: string, externalProductId: string, productId: ?string}> $batch
     */
    private function upsertBatch(array $batch, Context $context): int
    {
        if ($batch === []) {
            return 0;
        }

        $this->repository->upsert($batch, $context);

        return count($batch);
    }
}
