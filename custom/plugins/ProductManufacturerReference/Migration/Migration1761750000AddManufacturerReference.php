<?php

declare(strict_types=1);

namespace ProductManufacturerReference\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1761750000AddManufacturerReference extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1761750000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            ALTER TABLE `product`
            ADD COLUMN `manufacturer_reference` VARCHAR(255) NULL
        SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
