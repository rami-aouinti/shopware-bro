<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1793322100ImportJob extends MigrationStep
{
    #[\Override]
    public function getCreationTimestamp(): int
    {
        return 1793322100;
    }

    #[\Override]
    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `tp_import_job` (
 `id` BINARY(16) NOT NULL,
 `source` VARCHAR(100) NOT NULL,
 `status` VARCHAR(32) NOT NULL,
 `received` INT UNSIGNED NOT NULL DEFAULT 0,
 `accepted` INT UNSIGNED NOT NULL DEFAULT 0,
 `rejected` INT UNSIGNED NOT NULL DEFAULT 0,
 `error_summary` LONGTEXT NULL,
 `started_at` DATETIME(3) NULL,
 `finished_at` DATETIME(3) NULL,
 `created_at` DATETIME(3) NOT NULL,
 `updated_at` DATETIME(3) NULL,
 PRIMARY KEY (`id`),
 KEY `idx_tp_import_job_status_started` (`status`, `started_at`),
 KEY `idx_tp_import_job_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    #[\Override]
    public function updateDestructive(Connection $connection): void
    {
    }
}
