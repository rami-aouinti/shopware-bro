<?php declare(strict_types=1);
namespace SupplierIntegration\Command;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use SupplierIntegration\Service\OfferSearchIndexerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
#[AsCommand(name: 'supplier-integration:reindex', description: 'Rebuild the supplier-offer OpenSearch index from DAL records.')]
class ReindexSupplierOffersCommand extends Command
{
    public function __construct(
        #[Autowire(service: 'supplier_integration_offer.repository')] private readonly EntityRepository $offers,
        private readonly OfferSearchIndexerInterface $indexer
    ) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        $page = 1;
        $limit = 250;
        $total = 0;
        try {
            $this->indexer->deleteIndex();
            do {
                $criteria = new Criteria();
                $criteria->setLimit($limit);
                $criteria->setOffset(($page - 1) * $limit);
                $criteria->addFilter(new EqualsFilter('isActive', true));
                $result = $this->offers->search($criteria, $context);
                $documents = [];
                foreach ($result->getEntities() as $offer) {
                    $documents[] = [
                        'id' => $offer->getId(),
                        'supplierId' => $offer->getSupplierId(),
                        'externalId' => $offer->getExternalId(),
                        'sku' => $offer->getSku(),
                        'name' => $offer->getName(),
                        'category' => $offer->getCategory(),
                        'price' => $offer->getPrice(),
                        'currency' => $offer->getCurrency(),
                        'stock' => $offer->getStock(),
                        'deliveryDays' => $offer->getDeliveryDays(),
                        'isActive' => $offer->isActive(),
                    ];
                }
                if ($documents !== []) {
                    $this->indexer->index($documents);
                    $total += count($documents);
                }
                $hasNextPage = count($documents) === $limit;
                ++$page;
            } while ($hasNextPage);
            $output->writeln(sprintf('<info>Indexed %d active supplier offers.</info>', $total));
            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>Reindex failed: ' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
