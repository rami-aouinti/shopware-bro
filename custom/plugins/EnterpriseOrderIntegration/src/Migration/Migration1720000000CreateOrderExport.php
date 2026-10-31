<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Migration;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
class Migration1720000000CreateOrderExport extends MigrationStep
{
    public function getCreationTimestamp(): int { return 1720000000; }
    public function update(Connection $connection): void
    {
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS enterprise_order_export (
          id BINARY(16) NOT NULL,
          order_id BINARY(16) NOT NULL,
          status VARCHAR(32) NOT NULL DEFAULT 'pending',
          attempts INT UNSIGNED NOT NULL DEFAULT 0,
          correlation_id VARCHAR(64) NOT NULL,
          erp_reference VARCHAR(128) NULL,
          last_error VARCHAR(1000) NULL,
          next_attempt_at DATETIME(3) NULL,
          created_at DATETIME(3) NOT NULL,
          updated_at DATETIME(3) NULL,
          PRIMARY KEY (id),
          UNIQUE KEY uniq_eoi_order_id (order_id),
          KEY idx_eoi_status_retry (status, next_attempt_at),
          CONSTRAINT fk_eoi_order FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    public function updateDestructive(Connection $connection): void {}
}