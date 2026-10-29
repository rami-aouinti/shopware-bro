<?php declare(strict_types=1);
namespace SupplierIntegration\\Core\\Content\\SupplierOffer;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Entity;
use SupplierIntegration\\Core\\Content\\Supplier\\SupplierEntity;
class SupplierOfferEntity extends Entity
{
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
    public function getSupplierId(): string { return $this->supplierId; }
    public function getExternalId(): string { return $this->externalId; }
    public function getSku(): string { return $this->sku; }
    public function getName(): string { return $this->name; }
    public function getCategory(): ?string { return $this->category; }
    public function getPrice(): float { return $this->price; }
    public function getCurrency(): string { return $this->currency; }
    public function getStock(): int { return $this->stock; }
    public function getDeliveryDays(): int { return $this->deliveryDays; }
    public function getPayloadHash(): ?string { return $this->payloadHash; }
    public function isActive(): bool { return $this->isActive; }
    public function getSupplier(): ?SupplierEntity { return $this->supplier; }
}
