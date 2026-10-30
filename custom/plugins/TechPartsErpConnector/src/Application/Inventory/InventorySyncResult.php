<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Inventory;

final readonly class InventorySyncResult
{
    private function __construct(public bool $applied, public int $sellableQuantity)
    {
    }

    public static function applied(int $sellableQuantity): self
    {
        return new self(true, $sellableQuantity);
    }

    public static function duplicate(): self
    {
        return new self(false, 0);
    }
}
