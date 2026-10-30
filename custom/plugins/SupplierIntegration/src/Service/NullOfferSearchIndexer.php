<?php declare(strict_types=1);

namespace SupplierIntegration\Service;

/**
 * Safe local fallback: database remains source of truth until an OpenSearch endpoint is configured.
 */
class NullOfferSearchIndexer implements OfferSearchIndexerInterface
{
    public function index(array $documents): void
    {
    }

    public function deleteIndex(): void
    {
    }
}
