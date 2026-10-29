<?php declare(strict_types=1);
namespace SupplierIntegration\Storefront\Controller;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route(defaults: ['_routeScope' => ['storefront']])]
final class SupplierOffersController extends StorefrontController
{
    public function __construct(
        #[Autowire(service: 'supplier_integration_offer.repository')] private readonly EntityRepository $offerRepository
    ) {}
    #[Route(path: '/supplier-offers', name: 'frontend.supplier_integration.offers', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $category = trim((string) $request->query->get('category', ''));
        $availability = (string) $request->query->get('availability', 'all');
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->setLimit(500);
        $criteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));
        $result = $this->offerRepository->search($criteria, $context);
        $offers = [];
        foreach ($result->getEntities() as $entity) {
            $offers[] = [
                'name' => $entity->getName(),
                'sku' => $entity->getSku(),
                'brand' => 'Supplier',
                'category' => $entity->getCategory() ?? 'Other',
                'description' => 'Supplier offer imported into Shopware DAL.',
                'purchasePrice' => $entity->getPrice(),
                'currency' => $entity->getCurrency(),
                'availableQuantity' => $entity->getStock(),
                'deliveryDays' => $entity->getDeliveryDays(),
                'externalProductId' => $entity->getExternalId(),
            ];
        }
        $isDemoData = false;
        $supplier = ['name' => 'Imported suppliers'];
        if ($offers === []) {
            $demoFile = __DIR__ . '/../../Resources/demo/supplier-offers.json';
            $demoData = json_decode((string) file_get_contents($demoFile), true, 512, JSON_THROW_ON_ERROR);
            $offers = $demoData['items'] ?? [];
            $supplier = $demoData['supplier'] ?? ['name' => 'Demo Supplier'];
            $isDemoData = true;
        }
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $offers = array_values(array_filter($offers, static function (array $offer) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [(string) ($offer['name'] ?? ''), (string) ($offer['sku'] ?? ''), (string) ($offer['brand'] ?? ''), (string) ($offer['category'] ?? '')]));
                return str_contains($haystack, $needle);
            }));
        }
        if ($category !== '') {
            $offers = array_values(array_filter($offers, static fn (array $offer): bool => ($offer['category'] ?? '') === $category));
        }
        if ($availability === 'available') {
            $offers = array_values(array_filter($offers, static fn (array $offer): bool => (int) ($offer['availableQuantity'] ?? 0) > 0));
        } elseif ($availability === 'unavailable') {
            $offers = array_values(array_filter($offers, static fn (array $offer): bool => (int) ($offer['availableQuantity'] ?? 0) <= 0));
        }
        $categories = array_values(array_unique(array_map(static fn (array $offer): string => (string) ($offer['category'] ?? ''), $offers)));
        sort($categories);
        return $this->renderStorefront('@SupplierIntegration/storefront/page/supplier-offers/index.html.twig', [
            'supplier' => $supplier, 'offers' => $offers, 'categories' => $categories,
            'filters' => ['q' => $search, 'category' => $category, 'availability' => $availability],
            'totalOffers' => count($offers), 'isDemoData' => $isDemoData,
        ]);
    }
}
