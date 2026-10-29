<?php declare(strict_types=1);
namespace SupplierIntegration\\Command;
use Shopware\\Core\\Framework\\Context;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Search\\Criteria;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Search\\Filter\\EqualsFilter;
use Shopware\\Core\\Framework\\Uuid\\Uuid;
use SupplierIntegration\\Core\\Content\\SupplierOffer\\SupplierOfferDefinition;
use Symfony\\Component\\Console\\Attribute\\AsCommand;
use Symfony\\Component\\Console\\Command\\Command;
use Symfony\\Component\\Console\\Input\\InputInterface;
use Symfony\\Component\\Console\\Output\\OutputInterface;
use Symfony\\Component\\DependencyInjection\\Attribute\\Autowire;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\EntityRepository;
#[AsCommand(name: 'supplier-integration:reindex', description: 'Rebuild the supplier-offer search index (database fallback adapter).')]
class ReindexSupplierOffersCommand extends Command
{
    public function __construct(
        #[Autowire(service: 'supplier_integration_offer.repository')] private readonly EntityRepository $offers
    ) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->setLimit(500);
        $criteria->addFilter(new EqualsFilter('isActive', true));
        $result = $this->offers->search($criteria, $context);
        $output->writeln(sprintf('Reindex preparation complete: %d active offers read. Configure an OpenSearch adapter to publish documents.', $result->getTotal()));
        return Command::SUCCESS;
    }
}
