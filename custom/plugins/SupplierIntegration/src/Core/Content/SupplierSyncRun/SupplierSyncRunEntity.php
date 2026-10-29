<?php declare(strict_types=1);
namespace SupplierIntegration\Core\Content\SupplierSyncRun;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use SupplierIntegration\Core\Content\Supplier\SupplierEntity;
class SupplierSyncRunEntity extends Entity
{
    protected string $supplierId = '';
    protected string $status = 'queued';
    protected ?string $correlationId = null;
    protected ?\\DateTimeInterface $startedAt = null;
    protected ?\\DateTimeInterface $finishedAt = null;
    protected int $itemsReceived = 0;
    protected int $itemsCreated = 0;
    protected int $itemsUpdated = 0;
    protected int $itemsFailed = 0;
    protected ?string $lastError = null;
    protected ?SupplierEntity $supplier = null;
    protected ?EntityCollection $errors = null;
    public function getSupplierId(): string { return $this->supplierId; }
    public function getStatus(): string { return $this->status; }
    public function getCorrelationId(): ?string { return $this->correlationId; }
    public function getStartedAt(): ?\\DateTimeInterface { return $this->startedAt; }
    public function getFinishedAt(): ?\\DateTimeInterface { return $this->finishedAt; }
    public function getItemsReceived(): int { return $this->itemsReceived; }
    public function getItemsCreated(): int { return $this->itemsCreated; }
    public function getItemsUpdated(): int { return $this->itemsUpdated; }
    public function getItemsFailed(): int { return $this->itemsFailed; }
    public function getLastError(): ?string { return $this->lastError; }
    public function getSupplier(): ?SupplierEntity { return $this->supplier; }
    public function getErrors(): ?EntityCollection { return $this->errors; }
}
