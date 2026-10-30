<?php declare(strict_types=1);

namespace SupplierIntegration\Core\Content\SupplierOffer;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use SupplierIntegration\Core\Content\Supplier\SupplierEntity;

class SupplierOfferEntity extends Entity
{
    protected string $id;
    protected string $supplierId = '';
    protected string $externalId = '';
    protected string $sku = '';
    protected string $name = '';
    protected ?string $category = null;
    protected float $price = 0.0;
    protected string $currency = 'EUR';
    protected int $stock = 0;
    protected int $deliveryDays = 0;
    protected ?string $payloadHash = null;
    protected bool $isActive = true;
    protected ?SupplierEntity $supplier = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }
    public function getSupplierId(): string { return $this->supplierId; }
    public function setSupplierId(string $value): void { $this->supplierId = $value; }
    public function getExternalId(): string { return $this->externalId; }
    public function setExternalId(string $value): void { $this->externalId = $value; }
    public function getSku(): string { return $this->sku; }
    public function setSku(string $value): void { $this->sku = $value; }
    public function getName(): string { return $this->name; }
    public function setName(string $value): void { $this->name = $value; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $value): void { $this->category = $value; }
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $value): void { $this->price = $value; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $value): void { $this->currency = $value; }
    public function getStock(): int { return $this->stock; }
    public function setStock(int $value): void { $this->stock = $value; }
    public function getDeliveryDays(): int { return $this->deliveryDays; }
    public function setDeliveryDays(int $value): void { $this->deliveryDays = $value; }
    public function getPayloadHash(): ?string { return $this->payloadHash; }
    public function setPayloadHash(?string $value): void { $this->payloadHash = $value; }
    public function isActive(): bool { return $this->isActive; }
    public function getIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $value): void { $this->isActive = $value; }
    public function getSupplier(): ?SupplierEntity { return $this->supplier; }
    public function setSupplier(?SupplierEntity $value): void { $this->supplier = $value; }
}
