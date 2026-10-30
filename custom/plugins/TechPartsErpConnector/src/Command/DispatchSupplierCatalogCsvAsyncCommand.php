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
use Symfony\Component\Messenger\MessageBusInterface;
use TechPartsErpConnector\Application\Import\CsvSupplierCatalogReader;
use TechPartsErpConnector\Application\Import\ImportBatchStore;
use TechPartsErpConnector\Application\Import\ImportJobTracker;
use TechPartsErpConnector\Application\Import\SupplierBatchChunker;
use TechPartsErpConnector\Application\Message\ProcessSupplierBatchMessage;

#[AsCommand(
    name: 'techparts:erp:dispatch-supplier-csv-async',
    description: 'Persist bounded supplier CSV batches and queue only batch IDs',
)]
final class DispatchSupplierCatalogCsvAsyncCommand extends Command
{
    public function __construct(
        private readonly CsvSupplierCatalogReader $reader,
        private readonly SupplierBatchChunker $chunker,
        private readonly ImportBatchStore $batches,
        private readonly ImportJobTracker $jobs,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Readable supplier CSV file')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Max rows per durable batch', '100');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = (string) $input->getArgument('file');
        $batchSize = filter_var($input->getOption('batch-size'), FILTER_VALIDATE_INT);
        if (!is_file($file) || !is_readable($file) || $batchSize === false || $batchSize < 1 || $batchSize > 1000) {
            $output->writeln('<error>Provide a readable file and a batch size between 1 and 1000.</error>');
            return Command::INVALID;
        }

        $jobId = $this->jobs->start('async-csv:' . basename($file), Context::createCLIContext());
        $count = 0;
        try {
            // Stream the input file. Only one batch resides in PHP memory at a time.
            foreach ($this->chunker->chunk($this->reader->read($file), $batchSize) as $rows) {
                $this->batches->add($jobId, $count++, $rows);
            }

            if ($count === 0) {
                $this->jobs->finish($jobId, 0, 0, 0, Context::createCLIContext());
                $output->writeln('<info>Empty CSV; job finished.</info>');
                return Command::SUCCESS;
            }

            // All batches exist before the first worker can finish its job.
            foreach ($this->batches->pendingIds($jobId) as $batchId) {
                $this->bus->dispatch(new ProcessSupplierBatchMessage($batchId, $jobId));
            }
        } catch (\Throwable $exception) {
            $output->writeln('<error>Dispatch interrupted: ' . $exception->getMessage() . '</error>');
            $output->writeln(sprintf(
                '<comment>Job %s can be resumed with techparts:erp:resume-supplier-import %s</comment>',
                $jobId,
                $jobId
            ));
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Queued %d durable batches for job %s</info>', $count, $jobId));
        return Command::SUCCESS;
    }
}
