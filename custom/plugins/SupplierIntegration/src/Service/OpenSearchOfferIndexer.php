<?php declare(strict_types=1);
namespace SupplierIntegration\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class OpenSearchOfferIndexer implements OfferSearchIndexerInterface
{
    public function __construct(private readonly HttpClientInterface $httpClient) {}
    public function index(array $documents): void
    {
        $base = rtrim((string) getenv('OPENSEARCH_URL'), '/');
        if ($base === '') { throw new \RuntimeException('OPENSEARCH_URL is not configured.'); }
        $index = (string) (getenv('OPENSEARCH_INDEX') ?: 'supplier-offers');
        $lines = [];
        foreach ($documents as $doc) {
            if (empty($doc['id'])) { continue; }
            $lines[] = json_encode(['index' => ['_index' => $index, '_id' => $doc['id']]], JSON_THROW_ON_ERROR);
            $lines[] = json_encode($doc, JSON_THROW_ON_ERROR);
        }
        if ($lines === []) { return; }
        $response = $this->httpClient->request('POST', $base . '/_bulk', [
            'headers' => ['Content-Type' => 'application/x-ndjson'],
            'body' => implode("\n", $lines) . "\n",
            'timeout' => 20,
        ]);
        if ($response->getStatusCode() >= 300) { throw new \RuntimeException('OpenSearch bulk indexing failed: HTTP ' . $response->getStatusCode()); }
        $body = $response->toArray(false);
        if (($body['errors'] ?? false) === true) { throw new \RuntimeException('OpenSearch reported one or more document indexing errors.'); }
    }
    public function deleteIndex(): void
    {
        $base = rtrim((string) getenv('OPENSEARCH_URL'), '/');
        if ($base === '') { throw new \RuntimeException('OPENSEARCH_URL is not configured.'); }
        $index = (string) (getenv('OPENSEARCH_INDEX') ?: 'supplier-offers');
        $response = $this->httpClient->request('DELETE', $base . '/' . rawurlencode($index), ['timeout' => 10]);
        if (!in_array($response->getStatusCode(), [200, 404], true)) { throw new \RuntimeException('OpenSearch index deletion failed.'); }
    }
}
