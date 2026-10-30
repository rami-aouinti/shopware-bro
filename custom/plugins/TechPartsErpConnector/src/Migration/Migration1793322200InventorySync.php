<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1793322200InventorySync extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1793322200;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS tp_inventory_sync (
    id BINARY(16) NOT NULL,
    source VARCHAR(40) NOT NULL,
    external_event_id VARCHAR(128) NOT NULL,
    product_id BINARY(16) NOT NULL,
    external_quantity INT UNSIGNED NOT NULL,
    safety_stock INT UNSIGNED NOT NULL DEFAULT 0,
    applied_stock INT UNSIGNED NOT NULL,
    status VARCHAR(16) NOT NULL,
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_tp_inventory_source_event (source, external_event_id),
    KEY idx_tp_inventory_product_created (product_id, created_at),
    KEY idx_tp_inventory_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
