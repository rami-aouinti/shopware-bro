<?php declare(strict_types=1);

namespace SupplierIntegration\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use SupplierIntegration\Core\Content\Supplier\SupplierCollection;
use SupplierIntegration\Message\StartSupplierSyncMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'supplier-integration:sync', description: 'Queue an import for a supplier ID.')]
class StartSupplierSyncCommand extends Command
{
    public function __construct(
        /**
         * @var EntityRepository<SupplierCollection>
         */
        #[Autowire(service: 'supplier_integration_supplier.repository')]
        private readonly EntityRepository $suppliers,
        #[Autowire(service: 'supplier_integration_sync_run.repository')]
        private readonly EntityRepository $runs,
        private readonly MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('supplier-id', InputArgument::REQUIRED, 'Shopware UUID (32 hex characters) of the supplier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $supplierId = (string) $input->getArgument('supplier-id');
        if (!Uuid::isValid($supplierId)) {
            $output->writeln('<error>supplier-id must be a valid Shopware UUID.</error>');

            return Command::INVALID;
        }

        $context = Context::createCLIContext();
        $criteria = new Criteria([$supplierId]);
        if ($this->suppliers->search($criteria, $context)->getEntities()->first() === null) {
            $output->writeln('<error>Supplier not found.</error>');

            return Command::FAILURE;
        }

        $runId = Uuid::randomHex();
        $this->runs->create([[
            'id' => $runId,
            'supplierId' => $supplierId,
            'status' => 'queued',
            'correlationId' => Uuid::randomHex(),
            'itemsReceived' => 0,
            'itemsCreated' => 0,
            'itemsUpdated' => 0,
            'itemsFailed' => 0,
        ]], $context);

        $this->bus->dispatch(new StartSupplierSyncMessage($supplierId, $runId));
        $output->writeln(sprintf('Queued supplier sync run %s', $runId));

        return Command::SUCCESS;
    }
}
