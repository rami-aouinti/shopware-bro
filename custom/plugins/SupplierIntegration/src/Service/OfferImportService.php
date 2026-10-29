<?php declare(strict_types=1);
namespace SupplierIntegration\Service;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
class OfferImportService
{
    public function __construct(
        private readonly SupplierApiClientInterface $apiClient,
        #[Autowire(service: 'supplier_integration_offer.repository')] private readonly EntityRepository $offers,
        #[Autowire(service: 'supplier_integration_sync_run.repository')] private readonly EntityRepository $syncRuns,
        #[Autowire(service: 'supplier_integration_sync_error.repository')] private readonly EntityRepository $syncErrors,
    ) {}
    public function import(string $supplierId, string $syncRunId, Context $context): void
    {
        $received = $created = $updated = $failed = 0;
        $this->syncRuns->update([['id' => $syncRunId, 'status' => 'running', 'startedAt' => (new \DateTimeImmutable())->format(DATE_ATOM)]], $context);
        try {
            foreach ($this->apiClient->fetchOffers($supplierId) as $payload) {
                ++$received;
                $externalId = trim((string) ($payload['externalId'] ?? $payload['external_id'] ?? ''));
                $sku = trim((string) ($payload['sku'] ?? ''));
                $name = trim((string) ($payload['name'] ?? ''));
                if ($externalId === '' || $sku === '' || $name === '') {
                    ++$failed;
                    $this->syncErrors->create([['id' => Uuid::randomHex(), 'syncRunId' => $syncRunId, 'externalId' => $externalId ?: null, 'errorCode' => 'INVALID_PAYLOAD', 'message' => 'Offer requires externalId, sku and name.']], $context);
                    continue;
                }
                $normalized = [
                    'supplierId' => $supplierId, 'externalId' => $externalId, 'sku' => $sku, 'name' => $name,
                    'category' => $payload['category'] ?? null, 'price' => max(0, (float) ($payload['price'] ?? 0)),
                    'currency' => strtoupper((string) ($payload['currency'] ?? 'EUR')), 'stock' => max(0, (int) ($payload['stock'] ?? 0)),
                    'deliveryDays' => max(0, (int) ($payload['deliveryDays'] ?? $payload['delivery_days'] ?? 0)),
                    'payloadHash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)), 'isActive' => true,
                ];
                // DAL upsert + DB unique key (supplier_id, external_id) makes repeated deliveries idempotent.
                $normalized['id'] = Uuid::fromString(hash('sha256', $supplierId . ':' . $externalId, true));
                $this->offers->upsert([$normalized], $context);
                ++$created;
            }
            $this->syncRuns->update([['id' => $syncRunId, 'status' => $failed > 0 ? 'completed_with_errors' : 'completed', 'finishedAt' => (new \DateTimeImmutable())->format(DATE_ATOM), 'itemsReceived' => $received, 'itemsCreated' => $created, 'itemsUpdated' => $updated, 'itemsFailed' => $failed]], $context);
        } catch (\Throwable $e) {
            $this->syncRuns->update([['id' => $syncRunId, 'status' => 'failed', 'finishedAt' => (new \DateTimeImmutable())->format(DATE_ATOM), 'itemsReceived' => $received, 'itemsCreated' => $created, 'itemsUpdated' => $updated, 'itemsFailed' => $failed, 'lastError' => mb_substr($e->getMessage(), 0, 60000)]], $context);
            throw $e;
        }
    }
}
