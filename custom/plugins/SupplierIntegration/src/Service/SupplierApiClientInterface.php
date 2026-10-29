<?php declare(strict_types=1);
namespace SupplierIntegration\Service;
interface SupplierApiClientInterface
{
    /** @return iterable<array<string, mixed>> */
    public function fetchOffers(string $supplierId): iterable;
}
