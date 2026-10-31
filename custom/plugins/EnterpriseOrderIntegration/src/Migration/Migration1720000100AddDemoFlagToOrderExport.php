<?php declare(strict_types=1);

namespace EnterpriseOrderIntegration\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1720000100AddDemoFlagToOrderExport extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1720000100;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            "ALTER TABLE enterprise_order_export ADD COLUMN is_demo TINYINT(1) NOT NULL DEFAULT 0 AFTER next_attempt_at",
        );
        $connection->executeStatement(
            'CREATE INDEX idx_eoi_demo_created ON enterprise_order_export (is_demo, created_at)',
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
