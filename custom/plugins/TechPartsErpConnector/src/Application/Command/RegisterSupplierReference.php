<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Command;

final readonly class RegisterSupplierReference
{
    public function __construct(public string $supplierCode, public string $externalProductId, public ?string $productId = null) {}
}
