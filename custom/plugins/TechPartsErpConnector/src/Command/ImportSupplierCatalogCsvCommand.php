<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Command;

use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TechPartsErpConnector\Application\Import\CsvSupplierCatalogReader;
use TechPartsErpConnector\Application\Import\ImportJobTracker;
use TechPartsErpConnector\Application\Import\SupplierImportService;

#[AsCommand(
    name: 'techparts:import-supplier-catalog-csv',
    description: 'Import supplier product references from a CSV file',
)]
final class ImportSupplierCatalogCsvCommand extends Command
{
    public function __construct(
        private readonly CsvSupplierCatalogReader $reader,
        private readonly SupplierImportService $importer,
        private readonly ImportJobTracker $jobs,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Absolute path to the CSV file')
            ->addOption('delimiter', null, InputOption::VALUE_REQUIRED, 'One-character CSV delimiter', ',');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = (string) $input->getArgument('file');
        $delimiter = (string) $input->getOption('delimiter');

        if (!is_file($path) || !is_readable($path)) {
            $io->error('CSV file does not exist or is not readable.');
            return Command::INVALID;
        }

        if (strlen($delimiter) !== 1) {
            $io->error('The delimiter must be exactly one character.');
            return Command::INVALID;
        }

        $context = Context::createDefaultContext();
        $jobId = $this->jobs->start('csv:' . basename($path), $context);

        try {
            $result = $this->importer->import($this->reader->read($path, $delimiter), $context);
            $summary = $result->errors === []
                ? null
                : json_encode(array_slice($result->errors, 0, 50), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $this->jobs->finish(
                $jobId,
                $result->received,
                $result->accepted,
                $result->rejected,
                $context,
                $summary === false ? 'Some CSV rows were rejected; see application logs.' : $summary,
            );

            $io->table(
                ['Metric', 'Count'],
                [
                    ['Received', $result->received],
                    ['Imported/upserted', $result->accepted],
                    ['Rejected', $result->rejected],
                    ['Import job ID', $jobId],
                ],
            );

            if ($result->rejected > 0) {
                $io->warning(sprintf('%d row(s) were rejected. The job details contain a summary of the first 50 errors.', $result->rejected));
                return Command::FAILURE;
            }

            $io->success('Supplier catalog CSV import completed.');
            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            try {
                $this->jobs->fail($jobId, $exception::class . ': ' . $exception->getMessage(), $context);
            } catch (\Throwable $trackingException) {
                // Preserve the original import failure; the tracking failure is reported separately.
                $io->warning('Could not update import job status: ' . $trackingException->getMessage());
            }

            $io->error('Import failed: ' . $exception->getMessage());
            return Command::FAILURE;
        }
    }
}
