<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Minimal operational signal for cron/system monitoring.
 * Exit code 1 signals failed batches or import jobs; does not leak source payloads.
 */
#[AsCommand(
    name: 'techparts:erp:metrics',
    description: 'Report ERP job/batch counts and return failure on unresolved errors',
)]
final class ErpMetricsCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobs = $this->connection->fetchAllKeyValue(
            'SELECT status, COUNT(*) FROM tp_import_job GROUP BY status',
        );
        $batches = $this->connection->fetchAllKeyValue(
            'SELECT status, COUNT(*) FROM tp_import_batch GROUP BY status',
        );
        $stale = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tp_import_job WHERE status = \'running\' AND started_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
        );

        $summary = [
            'jobs' => array_map('intval', $jobs),
            'batches' => array_map('intval', $batches),
            'staleRunningJobs' => $stale,
        ];
        $output->writeln(json_encode($summary, JSON_THROW_ON_ERROR));

        return ((int) ($jobs['failed'] ?? 0) + (int) ($batches['failed'] ?? 0) + $stale) > 0
            ? Command::FAILURE
            : Command::SUCCESS;
    }
}
