<?php declare(strict_types=1);

namespace SupplierIntegration\Service;

interface OfferSearchIndexerContract
{
    /**
     * @param list<array<string, mixed>> $documents
     */
    public function index(array $documents): void;

    public function deleteIndex(): void;
}
