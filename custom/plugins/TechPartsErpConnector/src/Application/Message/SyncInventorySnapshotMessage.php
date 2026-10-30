<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Message;

final readonly class SyncInventorySnapshotMessage
{
    public function __construct(
        public string $source,
        public string $eventId,
        public string $productId,
        public int $availableQuantity,
        public int $safetyStock = 0,
    ) {
    }
}
