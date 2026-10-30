<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Model;

use Shopware\Core\Framework\Uuid\Uuid;

final readonly class StockSnapshot
{
    public function __construct(
        public string $source,
        public string $eventId,
        public string $productId,
        public int $availableQuantity,
        public int $safetyStock = 0,
        public ?int $version = null,
    ) {
        if (!preg_match('/^[A-Z0-9_-]{2,40}$/', $source)) {
            throw new \InvalidArgumentException('Stock source must be 2-40 uppercase letters, digits, underscores or hyphens');
        }
        if (trim($eventId) === '' || mb_strlen($eventId) > 128) {
            throw new \InvalidArgumentException('Stock event ID must contain 1-128 characters');
        }
        if (!Uuid::isValid($productId)) {
            throw new \InvalidArgumentException('A valid Shopware product UUID is required');
        }
        if ($version !== null && $version < 0) {
            throw new \InvalidArgumentException('ERP stock version cannot be negative');
        }
        if ($availableQuantity < 0 || $safetyStock < 0) {
            throw new \InvalidArgumentException('Stock quantities cannot be negative');
        }
    }

    public function sellableQuantity(): int
    {
        return max(0, $this->availableQuantity - $this->safetyStock);
    }
}
