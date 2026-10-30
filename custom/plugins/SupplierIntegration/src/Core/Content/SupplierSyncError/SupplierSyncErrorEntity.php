<?php declare(strict_types=1);

namespace SupplierIntegration\Core\Content\SupplierSyncError;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use SupplierIntegration\Core\Content\SupplierSyncRun\SupplierSyncRunEntity;

class SupplierSyncErrorEntity extends Entity
{
    protected string $id;
    protected string $syncRunId = '';
    protected ?string $externalId = null;
    protected string $errorCode = '';
    protected string $message = '';
    protected ?string $context = null;
    protected ?SupplierSyncRunEntity $syncRun = null;

    public function getId(): string { return $this->id; }
    public function getSyncRunId(): string { return $this->syncRunId; }
    public function setSyncRunId(string $value): void { $this->syncRunId = $value; }
    public function getExternalId(): ?string { return $this->externalId; }
    public function setExternalId(?string $value): void { $this->externalId = $value; }
    public function getErrorCode(): string { return $this->errorCode; }
    public function setErrorCode(string $value): void { $this->errorCode = $value; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $value): void { $this->message = $value; }
    public function getContext(): ?string { return $this->context; }
    public function setContext(?string $value): void { $this->context = $value; }
    public function getSyncRun(): ?SupplierSyncRunEntity { return $this->syncRun; }
    public function setSyncRun(?SupplierSyncRunEntity $value): void { $this->syncRun = $value; }
}
