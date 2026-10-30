<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

final readonly class SupplierImportRow
{
    public function __construct(public string $supplierCode, public string $externalProductId, public ?string $productId = null)
    {
    }
}
