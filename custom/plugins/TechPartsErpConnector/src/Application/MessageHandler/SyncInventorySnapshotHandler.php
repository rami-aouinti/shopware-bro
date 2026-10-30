<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\MessageHandler;

use Shopware\Core\Framework\Context;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use TechPartsErpConnector\Application\Inventory\InventorySyncService;
use TechPartsErpConnector\Application\Message\SyncInventorySnapshotMessage;
use TechPartsErpConnector\Domain\Model\StockSnapshot;

#[AsMessageHandler]
final readonly class SyncInventorySnapshotHandler
{
    public function __construct(private InventorySyncService $inventorySync)
    {
    }

    public function __invoke(SyncInventorySnapshotMessage $message): void
    {
        $snapshot = new StockSnapshot(
            $message->source,
            $message->eventId,
            $message->productId,
            $message->availableQuantity,
            $message->safetyStock,
            $message->version,
        );

        // Infrastructure errors intentionally bubble up so Messenger can retry.
        $this->inventorySync->synchronize($snapshot, Context::createCLIContext());
    }
}
