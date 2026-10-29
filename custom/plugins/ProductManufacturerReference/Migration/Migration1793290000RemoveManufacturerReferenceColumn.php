<?php

declare(strict_types=1);

namespace ProductManufacturerReference\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1793290000RemoveManufacturerReferenceColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1793290000;
    }

    public function update(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['product'])) {
            return;
        }

        $columns = $schemaManager->listTableColumns('product');

        if (isset($columns['manufacturer_reference'])) {
            $connection->executeStatement(
                'ALTER TABLE `product` DROP COLUMN `manufacturer_reference`'
            );
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
