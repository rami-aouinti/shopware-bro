<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1793322400ImportBatches extends MigrationStep
{
    #[\Override]
    public function getCreationTimestamp(): int
    {
        return 1793322400;
    }

    #[\Override]
    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS tp_import_batch (
    id CHAR(32) NOT NULL,
    job_id CHAR(32) NOT NULL,
    batch_index INT UNSIGNED NOT NULL,
    rows_json LONGTEXT NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pending',
    accepted INT UNSIGNED NOT NULL DEFAULT 0,
    rejected INT UNSIGNED NOT NULL DEFAULT 0,
    last_error TEXT NULL,
    created_at DATETIME(3) NOT NULL,
    updated_at DATETIME(3) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_tp_import_job_index (job_id, batch_index),
    KEY idx_tp_import_batch_pending (job_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    #[\Override]
    public function updateDestructive(Connection $connection): void
    {
    }
}
