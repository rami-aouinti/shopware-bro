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
        private readonly OfferSearchIndexerInterface $indexer,
    ) {}
    public function import(string $supplierId, string $syncRunId, Context $context): void
    {
        $received = $upserted = $failed = 0;
        $documents = [];
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
                $id = substr(hash('md5', $supplierId . ':' . $externalId), 0, 32);
                $normalized = [
                    'id' => $id, 'supplierId' => $supplierId, 'externalId' => $externalId, 'sku' => $sku, 'name' => $name,
                    'category' => $payload['category'] ?? null, 'price' => max(0, (float) ($payload['price'] ?? 0)),
                    'currency' => strtoupper((string) ($payload['currency'] ?? 'EUR')), 'stock' => max(0, (int) ($payload['stock'] ?? 0)),
                    'deliveryDays' => max(0, (int) ($payload['deliveryDays'] ?? $payload['delivery_days'] ?? 0)),
                    'payloadHash' => hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)), 'isActive' => true,
                ];
                $this->offers->upsert([$normalized], $context);
                $documents[] = [
                    'id' => $id, 'supplierId' => $supplierId, 'externalId' => $externalId,
                    'sku' => $sku, 'name' => $name, 'category' => $normalized['category'],
                    'price' => $normalized['price'], 'currency' => $normalized['currency'],
                    'stock' => $normalized['stock'], 'deliveryDays' => $normalized['deliveryDays'],
                    'isActive' => true,
                ];
                ++$upserted;
                if (count($documents) >= 100) {
                    $this->indexer->index($documents);
                    $documents = [];
                }
            }
            if ($documents !== []) { $this->indexer->index($documents); }
            $this->syncRuns->update([['id' => $syncRunId, 'status' => $failed > 0 ? 'completed_with_errors' : 'completed', 'finishedAt' => (new \DateTimeImmutable())->format(DATE_ATOM), 'itemsReceived' => $received, 'itemsCreated' => $upserted, 'itemsUpdated' => 0, 'itemsFailed' => $failed]], $context);
        } catch (\Throwable $e) {
            $this->syncRuns->update([['id' => $syncRunId, 'status' => 'failed', 'finishedAt' => (new \DateTimeImmutable())->format(DATE_ATOM), 'itemsReceived' => $received, 'itemsCreated' => $upserted, 'itemsUpdated' => 0, 'itemsFailed' => $failed, 'lastError' => mb_substr($e->getMessage(), 0, 60000)]], $context);
            throw $e;
        }
    }
}
