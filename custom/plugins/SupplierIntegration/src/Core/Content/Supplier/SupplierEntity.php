<?php declare(strict_types=1);
namespace SupplierIntegration\Core\Content\Supplier;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
class SupplierEntity extends Entity
{
    protected string $name = '';
    protected string $code = '';
    protected ?string $apiUrl = null;
    protected ?string $apiKeyEnv = null;
    protected bool $active = true;
    protected bool $demoMode = true;
    protected ?string $configuration = null;
    protected ?EntityCollection $offers = null;
    protected ?EntityCollection $syncRuns = null;
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getApiUrl(): ?string { return $this->apiUrl; }
    public function getApiKeyEnv(): ?string { return $this->apiKeyEnv; }
    public function isActive(): bool { return $this->active; }
    public function isDemoMode(): bool { return $this->demoMode; }
    public function getConfiguration(): ?string { return $this->configuration; }
    public function getOffers(): ?EntityCollection { return $this->offers; }
    public function getSyncRuns(): ?EntityCollection { return $this->syncRuns; }
}
