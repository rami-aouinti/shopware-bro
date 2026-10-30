<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Persistence\Dal;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class SupplierCatalogEntity extends Entity
{
    protected string $supplierCode;
    protected string $externalProductId;
    protected ?string $productId = null;

    public function getSupplierCode(): string { return $this->supplierCode; }
    public function setSupplierCode(string $code): void { $this->supplierCode = $code; }
    public function getExternalProductId(): string { return $this->externalProductId; }
    public function setExternalProductId(string $id): void { $this->externalProductId = $id; }
    public function getProductId(): ?string { return $this->productId; }
    public function setProductId(?string $id): void { $this->productId = $id; }
}
