<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1793322000SupplierCatalog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1793322000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `tp_supplier_catalog` (
    `id` BINARY(16) NOT NULL,
    `supplier_code` VARCHAR(40) NOT NULL,
    `external_product_id` VARCHAR(128) NOT NULL,
    `product_id` BINARY(16) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_tp_supplier_external` (`supplier_code`, `external_product_id`),
    KEY `idx_tp_supplier_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
