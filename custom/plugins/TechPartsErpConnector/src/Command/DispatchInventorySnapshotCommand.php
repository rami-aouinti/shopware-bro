<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TechPartsErpConnector\Application\Message\SyncInventorySnapshotMessage;

#[AsCommand(
    name: 'techparts:inventory:dispatch-snapshot',
    description: 'Queue an ERP inventory snapshot for asynchronous synchronization',
)]
final class DispatchInventorySnapshotCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('product-id', InputArgument::REQUIRED, '32-character Shopware product UUID')
            ->addArgument('event-id', InputArgument::REQUIRED, 'Stable ERP event ID reused on retries')
            ->addArgument('quantity', InputArgument::REQUIRED, 'ERP available quantity')
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'ERP source code', 'ACME_ERP')
            ->addOption('safety-stock', null, InputOption::VALUE_REQUIRED, 'Quantity reserved from sale', '0');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $quantity = filter_var($input->getArgument('quantity'), FILTER_VALIDATE_INT);
        $safetyStock = filter_var($input->getOption('safety-stock'), FILTER_VALIDATE_INT);

        if ($quantity === false || $safetyStock === false || $quantity < 0 || $safetyStock < 0) {
            $output->writeln('<error>quantity and safety-stock must be non-negative integers.</error>');

            return Command::INVALID;
        }

        try {
            $this->messageBus->dispatch(new SyncInventorySnapshotMessage(
                (string) $input->getOption('source'),
                (string) $input->getArgument('event-id'),
                (string) $input->getArgument('product-id'),
                $quantity,
                $safetyStock,
            ));
        } catch (\Throwable $exception) {
            $output->writeln('<error>Could not queue inventory snapshot: ' . $exception->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Inventory snapshot queued. Source: %s; event: %s</info>',
            (string) $input->getOption('source'),
            (string) $input->getArgument('event-id'),
        ));

        return Command::SUCCESS;
    }
}
