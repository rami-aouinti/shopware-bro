<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Persistence\Dal;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class SupplierCatalogEntity extends Entity
{
    protected string $id;

    protected string $supplierCode = '';

    protected string $externalProductId = '';

    protected ?string $productId = null;

    protected ?ProductEntity $product = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSupplierCode(): string
    {
        return $this->supplierCode;
    }

    public function setSupplierCode(string $code): void
    {
        $this->supplierCode = $code;
    }

    public function getExternalProductId(): string
    {
        return $this->externalProductId;
    }

    public function setExternalProductId(string $id): void
    {
        $this->externalProductId = $id;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $id): void
    {
        $this->productId = $id;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }
}
