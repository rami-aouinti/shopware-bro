<?php declare(strict_types=1);
namespace SupplierIntegration\\Migration;
use Doctrine\\DBAL\\Connection;
use Shopware\\Core\\Framework\\Migration\\MigrationStep;
class Migration1761700000CreateSupplierIntegrationTables extends MigrationStep
{
    public function getCreationTimestamp(): int { return 1761700000; }
    public function update(Connection $connection): void
    {
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS supplier_integration_supplier (
            id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(100) NOT NULL,
            api_url VARCHAR(1024) NULL, api_key_env VARCHAR(255) NULL, active TINYINT(1) NOT NULL DEFAULT 1,
            demo_mode TINYINT(1) NOT NULL DEFAULT 1, configuration LONGTEXT NULL,
            created_at DATETIME(3) NOT NULL, updated_at DATETIME(3) NULL,
            PRIMARY KEY (id), UNIQUE KEY uniq_supplier_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS supplier_integration_sync_run (
            id BINARY(16) NOT NULL, supplier_id BINARY(16) NOT NULL, status VARCHAR(32) NOT NULL,
            correlation_id VARCHAR(64) NULL, started_at DATETIME(3) NULL, finished_at DATETIME(3) NULL,
            items_received INT NOT NULL DEFAULT 0, items_created INT NOT NULL DEFAULT 0,
            items_updated INT NOT NULL DEFAULT 0, items_failed INT NOT NULL DEFAULT 0,
            last_error LONGTEXT NULL, created_at DATETIME(3) NOT NULL,
            PRIMARY KEY (id), KEY idx_sync_supplier_status (supplier_id, status),
            CONSTRAINT fk_supplier_sync_supplier FOREIGN KEY (supplier_id) REFERENCES supplier_integration_supplier (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS supplier_integration_offer (
            id BINARY(16) NOT NULL, supplier_id BINARY(16) NOT NULL, external_id VARCHAR(255) NOT NULL,
            sku VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) NULL,
            price DOUBLE NOT NULL DEFAULT 0, currency VARCHAR(3) NOT NULL DEFAULT 'EUR', stock INT NOT NULL DEFAULT 0,
            delivery_days INT NOT NULL DEFAULT 0, payload_hash CHAR(64) NULL, is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME(3) NOT NULL, updated_at DATETIME(3) NULL,
            PRIMARY KEY (id), UNIQUE KEY uniq_supplier_external_offer (supplier_id, external_id),
            KEY idx_supplier_offer_sku (sku), KEY idx_supplier_offer_active_price (is_active, price),
            CONSTRAINT fk_supplier_offer_supplier FOREIGN KEY (supplier_id) REFERENCES supplier_integration_supplier (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $connection->executeStatement("CREATE TABLE IF NOT EXISTS supplier_integration_sync_error (
            id BINARY(16) NOT NULL, sync_run_id BINARY(16) NOT NULL, external_id VARCHAR(255) NULL,
            error_code VARCHAR(100) NOT NULL, message LONGTEXT NOT NULL, context LONGTEXT NULL,
            created_at DATETIME(3) NOT NULL, PRIMARY KEY (id), KEY idx_sync_error_run (sync_run_id),
            CONSTRAINT fk_supplier_sync_error_run FOREIGN KEY (sync_run_id) REFERENCES supplier_integration_sync_run (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    public function updateDestructive(Connection $connection): void {}
}
