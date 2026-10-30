<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Inventory;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use TechPartsErpConnector\Domain\Model\StockSnapshot;

final readonly class InventorySyncService
{
    public function __construct(
        private Connection $connection,
        private EntityRepository $productRepository,
        private LoggerInterface $logger,
    ) {
    }

    /** Apply an authoritative ERP stock snapshot once per source/event ID. */
    public function synchronize(StockSnapshot $snapshot, Context $context): InventorySyncResult
    {
        $sellableQuantity = $snapshot->sellableQuantity();
        $eventId = Uuid::randomBytes();

        return $this->connection->transactional(function (Connection $connection) use (
            $snapshot, $context, $sellableQuantity, $eventId,
        ): InventorySyncResult {
            $inserted = $connection->executeStatement(
                <<<'SQL'
INSERT IGNORE INTO tp_inventory_sync (
    id, source, external_event_id, product_id, external_quantity,
    safety_stock, applied_stock, status, created_at, updated_at
) VALUES (
    :id, :source, :eventId, :productId, :externalQuantity,
    :safetyStock, :appliedStock, 'processing', NOW(3), NULL
)
SQL,
                [
                    'id' => $eventId,
                    'source' => $snapshot->source,
                    'eventId' => $snapshot->eventId,
                    'productId' => Uuid::fromHexToBytes($snapshot->productId),
                    'externalQuantity' => $snapshot->availableQuantity,
                    'safetyStock' => $snapshot->safetyStock,
                    'appliedStock' => $sellableQuantity,
                ],
            );

            if ($inserted === 0) {
                $this->logger->info('Duplicate ERP inventory event ignored', [
                    'source' => $snapshot->source,
                    'eventId' => $snapshot->eventId,
                    'productId' => $snapshot->productId,
                ]);

                return InventorySyncResult::duplicate();
            }

            $this->productRepository->update([[
                'id' => $snapshot->productId,
                'stock' => $sellableQuantity,
            ]], $context);

            $connection->update('tp_inventory_sync', [
                'status' => 'applied',
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'),
            ], ['id' => $eventId]);

            $this->logger->info('ERP inventory snapshot applied', [
                'source' => $snapshot->source,
                'eventId' => $snapshot->eventId,
                'productId' => $snapshot->productId,
                'externalQuantity' => $snapshot->availableQuantity,
                'safetyStock' => $snapshot->safetyStock,
                'sellableQuantity' => $sellableQuantity,
            ]);

            return InventorySyncResult::applied($sellableQuantity);
        });
    }
}
