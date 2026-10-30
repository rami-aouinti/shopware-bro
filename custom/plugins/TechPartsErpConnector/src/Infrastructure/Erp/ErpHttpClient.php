<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Erp;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ErpHttpClient
{
    public function __construct(private HttpClientInterface $httpClient, private ErpClientConfig $config) {}

    /** @return array<string, mixed> */
    public function get(string $path): array
    {
        if ($path === '' || str_starts_with($path, '//') || preg_match('#^[a-z][a-z0-9+.-]*:#i', $path)) {
            throw new \InvalidArgumentException('ERP path must be a relative API path');
        }
        try {
            $response = $this->httpClient->request('GET', rtrim($this->config->baseUrl, '/') . '/' . ltrim($path, '/'), [
                'timeout' => $this->config->timeoutSeconds,
                'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->config->apiToken],
            ]);
            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 300) {
                throw new ErpApiException(sprintf('ERP returned HTTP %d', $status), $status);
            }
            $data = $response->toArray(false);
            if (!is_array($data) || array_is_list($data)) {
                throw new ErpApiException('ERP response must be a JSON object', $status);
            }
            return $data;
        } catch (TransportExceptionInterface $exception) {
            throw new ErpApiException('ERP transport failure', null, $exception);
        }
    }
}
