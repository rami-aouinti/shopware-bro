<?php declare(strict_types=1);

namespace SupplierIntegration\Core\Content\SupplierSyncRun;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use SupplierIntegration\Core\Content\Supplier\SupplierEntity;

class SupplierSyncRunEntity extends Entity
{
    protected string $id;

    protected string $supplierId = '';

    protected string $status = 'queued';

    protected ?string $correlationId = null;

    protected ?\DateTimeInterface $startedAt = null;

    protected ?\DateTimeInterface $finishedAt = null;

    protected int $itemsReceived = 0;

    protected int $itemsCreated = 0;

    protected int $itemsUpdated = 0;

    protected int $itemsFailed = 0;

    protected ?string $lastError = null;

    protected ?SupplierEntity $supplier = null;

    protected ?EntityCollection $errors = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSupplierId(): string
    {
        return $this->supplierId;
    }

    public function setSupplierId(string $value): void
    {
        $this->supplierId = $value;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $value): void
    {
        $this->status = $value;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(?string $value): void
    {
        $this->correlationId = $value;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $value): void
    {
        $this->startedAt = $value;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $value): void
    {
        $this->finishedAt = $value;
    }

    public function getItemsReceived(): int
    {
        return $this->itemsReceived;
    }

    public function setItemsReceived(int $value): void
    {
        $this->itemsReceived = $value;
    }

    public function getItemsCreated(): int
    {
        return $this->itemsCreated;
    }

    public function setItemsCreated(int $value): void
    {
        $this->itemsCreated = $value;
    }

    public function getItemsUpdated(): int
    {
        return $this->itemsUpdated;
    }

    public function setItemsUpdated(int $value): void
    {
        $this->itemsUpdated = $value;
    }

    public function getItemsFailed(): int
    {
        return $this->itemsFailed;
    }

    public function setItemsFailed(int $value): void
    {
        $this->itemsFailed = $value;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $value): void
    {
        $this->lastError = $value;
    }

    public function getSupplier(): ?SupplierEntity
    {
        return $this->supplier;
    }

    public function setSupplier(?SupplierEntity $value): void
    {
        $this->supplier = $value;
    }

    public function getErrors(): ?EntityCollection
    {
        return $this->errors;
    }

    public function setErrors(?EntityCollection $value): void
    {
        $this->errors = $value;
    }
}
