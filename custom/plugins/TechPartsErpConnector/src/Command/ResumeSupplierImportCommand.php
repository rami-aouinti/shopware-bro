<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TechPartsErpConnector\Application\Import\ImportBatchStore;
use TechPartsErpConnector\Application\Message\ProcessSupplierBatchMessage;

#[AsCommand(
    name: 'techparts:erp:resume-supplier-import',
    description: 'Requeue pending durable batches for an interrupted supplier import',
)]
final class ResumeSupplierImportCommand extends Command
{
    public function __construct(private readonly ImportBatchStore $batches, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('job-id', InputArgument::REQUIRED, 'Existing import job UUID');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobId = (string) $input->getArgument('job-id');

        try {
            $pending = $this->batches->pendingIds($jobId);
            foreach ($pending as $batchId) {
                $this->bus->dispatch(new ProcessSupplierBatchMessage($batchId, $jobId));
            }
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Requeued %d pending batch(es).</info>', count($pending)));

        return Command::SUCCESS;
    }
}
