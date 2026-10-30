<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1793322300InventoryVersion extends MigrationStep
{
    #[\Override]
    public function getCreationTimestamp(): int
    {
        return 1793322300;
    }

    #[\Override]
    public function update(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();
        if (!$schemaManager->tablesExist(['tp_inventory_sync'])) {
            throw new \RuntimeException('Inventory sync table must be migrated first');
        }

        $columns = $schemaManager->listTableColumns('tp_inventory_sync');
        if (!isset($columns['event_version'])) {
            $connection->executeStatement(
                'ALTER TABLE tp_inventory_sync ADD event_version BIGINT UNSIGNED NULL AFTER applied_stock'
            );
        }
    }

    #[\Override]
    public function updateDestructive(Connection $connection): void
    {
    }
}
