<?php declare(strict_types=1);
namespace SupplierIntegration\\Service;
use Symfony\\Contracts\\HttpClient\\HttpClientInterface;
class HttpSupplierApiClient implements SupplierApiClientInterface
{
    public function __construct(private readonly HttpClientInterface $httpClient) {}
    public function fetchOffers(string $supplierId): iterable
    {
        $baseUrl = rtrim((string) getenv('SUPPLIER_API_BASE_URL'), '/');
        if ($baseUrl === '') { throw new \\RuntimeException('SUPPLIER_API_BASE_URL is not configured.'); }
        $token = (string) getenv('SUPPLIER_API_TOKEN');
        $page = 1;
        do {
            $response = $this->httpClient->request('GET', $baseUrl . '/offers', [
                'query' => ['page' => $page, 'supplierId' => $supplierId],
                'headers' => $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [],
                'timeout' => 15,
            ]);
            $status = $response->getStatusCode();
            if ($status === 429 || $status >= 500) { throw new \\RuntimeException('Temporary supplier API error: HTTP ' . $status); }
            if ($status >= 400) { throw new \\UnexpectedValueException('Supplier API rejected request: HTTP ' . $status); }
            $body = $response->toArray(false);
            foreach (($body['items'] ?? []) as $item) { yield $item; }
            $page++;
            $hasMore = (bool) ($body['hasMore'] ?? false);
        } while ($hasMore && $page <= 1000);
    }
}
