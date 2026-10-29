<?php declare(strict_types=1);

namespace SupplierIntegration\Storefront\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
final class SupplierOffersController extends StorefrontController
{
    #[Route(path: '/supplier-offers', name: 'frontend.supplier_integration.offers', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $demoFile = __DIR__ . '/../../Resources/demo/supplier-offers.json';
        $demoData = json_decode((string) file_get_contents($demoFile), true, 512, JSON_THROW_ON_ERROR);
        $offers = $demoData['items'] ?? [];
        $search = trim((string) $request->query->get('q', ''));
        $category = trim((string) $request->query->get('category', ''));
        $availability = (string) $request->query->get('availability', 'all');

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $offers = array_values(array_filter($offers, static function (array $offer) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($offer['name'] ?? ''),
                    (string) ($offer['sku'] ?? ''),
                    (string) ($offer['brand'] ?? ''),
                    (string) ($offer['category'] ?? ''),
                ]));

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

        $categories = array_values(array_unique(array_map(static fn (array $offer): string => (string) ($offer['category'] ?? ''), $demoData['items'] ?? [])));
        sort($categories);

        return $this->renderStorefront('@SupplierIntegration/storefront/page/supplier-offers/index.html.twig', [
            'supplier' => $demoData['supplier'] ?? [],
            'offers' => $offers,
            'categories' => $categories,
            'filters' => ['q' => $search, 'category' => $category, 'availability' => $availability],
            'totalOffers' => count($offers),
            'isDemoData' => true,
        ]);
    }
}
