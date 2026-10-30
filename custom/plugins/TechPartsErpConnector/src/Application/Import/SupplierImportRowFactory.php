<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

final class SupplierImportRowFactory
{
    /** @param array<string, mixed> $payload */
    public function fromArray(array $payload): SupplierImportRow
    {
        foreach (['supplierCode', 'externalProductId'] as $key) {
            if (!isset($payload[$key]) || !is_string($payload[$key])) {
                throw new \InvalidArgumentException(sprintf('Missing or invalid %s', $key));
            }
        }
        if (isset($payload['productId']) && !is_string($payload['productId'])) {
            throw new \InvalidArgumentException('Invalid productId type');
        }
        return new SupplierImportRow($payload['supplierCode'], $payload['externalProductId'], $payload['productId'] ?? null);
    }
}
