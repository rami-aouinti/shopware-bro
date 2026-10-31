<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Service;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
final class ErpClient
{
    public function __construct(private readonly HttpClientInterface $httpClient, private readonly SystemConfigService $systemConfig) {}
    public function exportOrder(array $payload, string $correlationId): ResponseInterface
    {
        $baseUrl = rtrim((string) $this->systemConfig->get('EnterpriseOrderIntegration.config.erpBaseUrl'), '/');
        if ($baseUrl === '') throw new \RuntimeException('ERP base URL is not configured.');
        $token = (string) $this->systemConfig->get('EnterpriseOrderIntegration.config.erpToken');
        return $this->httpClient->request('POST', $baseUrl . '/api/orders', [
            'timeout' => 8.0, 'max_duration' => 12.0,
            'headers' => array_filter(['Accept' => 'application/json', 'Content-Type' => 'application/json', 'X-Correlation-ID' => $correlationId, 'Authorization' => $token !== '' ? 'Bearer ' . $token : null]),
            'json' => $payload,
        ]);
    }
}