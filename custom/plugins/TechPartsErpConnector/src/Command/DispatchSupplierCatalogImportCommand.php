<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Command;

use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TechPartsErpConnector\Application\Import\ImportJobTracker;
use TechPartsErpConnector\Application\Message\SyncSupplierCatalogMessage;

#[AsCommand(
    name: 'techparts:erp:dispatch-supplier-import',
    description: 'Queue a supplier catalog JSON file for asynchronous ERP import',
)]
final class DispatchSupplierCatalogImportCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ImportJobTracker $jobTracker,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to a JSON file containing a list of supplier rows')
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Import source label', 'manual-json')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'DAL batch size (1-1000)', '100');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('file');
        if (!is_file($path) || !is_readable($path)) {
            $output->writeln('<error>Input file does not exist or is not readable.</error>');
            return Command::FAILURE;
        }

        $batchSize = filter_var($input->getOption('batch-size'), FILTER_VALIDATE_INT);
        if ($batchSize === false || $batchSize < 1 || $batchSize > 1000) {
            $output->writeln('<error>--batch-size must be an integer between 1 and 1000.</error>');
            return Command::INVALID;
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $output->writeln('<error>Input file is not valid JSON: '.$exception->getMessage().'</error>');
            return Command::INVALID;
        }

        if (!is_array($decoded) || !array_is_list($decoded) || $decoded === []) {
            $output->writeln('<error>JSON root must be a non-empty list of row objects.</error>');
            return Command::INVALID;
        }
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                $output->writeln('<error>Every row must be a JSON object.</error>');
                return Command::INVALID;
            }
        }

        $context = Context::createDefaultContext();
        $jobId = $this->jobTracker->start((string) $input->getOption('source'), $context);
        $this->messageBus->dispatch(new SyncSupplierCatalogMessage(
            $jobId,
            (string) $input->getOption('source'),
            $decoded,
            $batchSize,
        ));

        $output->writeln(sprintf('<info>Supplier import queued. Job ID: %s</info>', $jobId));
        return Command::SUCCESS;
    }
}
