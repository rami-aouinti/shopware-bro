<?php declare(strict_types=1);

namespace SupplierIntegration\Service;

class DemoSupplierApiClient implements SupplierApiClientInterface
{
    public function __construct(private readonly string $projectDir)
    {
    }

    public function fetchOffers(string $supplierId): iterable
    {
        $path = $this->projectDir . '/custom/plugins/SupplierIntegration/src/Resources/demo/supplier-offers.json';
        if (!is_file($path)) {
            throw new \RuntimeException('Supplier demo fixture not found: ' . $path);
        }
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        foreach (($data['offers'] ?? []) as $offer) {
            yield $offer;
        }
    }
}
