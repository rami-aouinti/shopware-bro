<?php declare(strict_types=1);
namespace SupplierIntegration\Core\Content\SupplierSyncError;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use SupplierIntegration\Core\Content\SupplierSyncRun\SupplierSyncRunEntity;
class SupplierSyncErrorEntity extends Entity
{
    protected string $syncRunId = '';
    protected ?string $externalId = null;
    protected string $errorCode = '';
    protected string $message = '';
    protected ?string $context = null;
    protected ?SupplierSyncRunEntity $syncRun = null;
    public function getSyncRunId(): string { return $this->syncRunId; }
    public function getExternalId(): ?string { return $this->externalId; }
    public function getErrorCode(): string { return $this->errorCode; }
    public function getMessage(): string { return $this->message; }
    public function getContext(): ?string { return $this->context; }
    public function getSyncRun(): ?SupplierSyncRunEntity { return $this->syncRun; }
}
