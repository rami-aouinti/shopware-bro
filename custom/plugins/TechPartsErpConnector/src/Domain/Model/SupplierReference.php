<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Model;

final readonly class SupplierReference
{
    public function __construct(public string $supplierCode, public string $externalProductId)
    {
        if (!preg_match('/^[A-Z0-9_-]{2,40}$/D', $supplierCode)) {
            throw new \InvalidArgumentException('Invalid supplier code');
        }
        if (trim($externalProductId) === '' || strlen($externalProductId) > 128) {
            throw new \InvalidArgumentException('Invalid external product ID');
        }
    }

    public function key(): string
    {
        return $this->supplierCode . ':' . $this->externalProductId;
    }
}
