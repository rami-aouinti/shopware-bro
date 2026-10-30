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
    public function __construct(private EntityRepository $repository, private LoggerInterface $logger) {}

    /**
     * Imports iterable rows in bounded DAL batches. A failed batch throws to the caller for retry.
     * Invalid rows are rejected and logged without aborting the entire import.
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
                    'id' => Uuid::fromStringToHex($reference->key()),
                    'supplierCode' => $reference->supplierCode,
                    'externalProductId' => $reference->externalProductId,
                    'productId' => $row->productId,
                ];
                if (count($batch) >= $batchSize) {
                    $this->repository->upsert($batch, $context);
                    $accepted += count($batch);
                    $batch = [];
                }
            } catch (\InvalidArgumentException $exception) {
                $errors[] = ['row' => $received, 'error' => $exception->getMessage()];
                $this->logger->warning('Supplier import row rejected', ['row' => $received, 'reason' => $exception->getMessage()]);
            }
        }
        if ($batch !== []) {
            $this->repository->upsert($batch, $context);
            $accepted += count($batch);
        }
        $this->logger->info('Supplier import finished', ['received' => $received, 'accepted' => $accepted, 'rejected' => count($errors)]);
        return new SupplierImportResult($received, $accepted, count($errors), $errors);
    }
}
