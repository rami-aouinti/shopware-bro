<?php declare(strict_types=1);

namespace SupplierIntegration\Core\Content\Supplier;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class SupplierEntity extends Entity
{
    protected string $id;
    protected string $name = '';
    protected string $code = '';
    protected ?string $apiUrl = null;
    protected ?string $apiKeyEnv = null;
    protected bool $active = true;
    protected bool $demoMode = true;
    protected ?string $configuration = null;
    protected ?EntityCollection $offers = null;
    protected ?EntityCollection $syncRuns = null;

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $value): void { $this->name = $value; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $value): void { $this->code = $value; }
    public function getApiUrl(): ?string { return $this->apiUrl; }
    public function setApiUrl(?string $value): void { $this->apiUrl = $value; }
    public function getApiKeyEnv(): ?string { return $this->apiKeyEnv; }
    public function setApiKeyEnv(?string $value): void { $this->apiKeyEnv = $value; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $value): void { $this->active = $value; }
    public function isDemoMode(): bool { return $this->demoMode; }
    public function setDemoMode(bool $value): void { $this->demoMode = $value; }
    public function getConfiguration(): ?string { return $this->configuration; }
    public function setConfiguration(?string $value): void { $this->configuration = $value; }
    public function getOffers(): ?EntityCollection { return $this->offers; }
    public function setOffers(?EntityCollection $offers): void { $this->offers = $offers; }
    public function getSyncRuns(): ?EntityCollection { return $this->syncRuns; }
    public function setSyncRuns(?EntityCollection $syncRuns): void { $this->syncRuns = $syncRuns; }
}
