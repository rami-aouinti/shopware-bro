<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use TechPartsErpConnector\Infrastructure\Erp\ErpApiException;
use TechPartsErpConnector\Infrastructure\Erp\ErpClientConfig;
use TechPartsErpConnector\Infrastructure\Erp\ErpHttpClient;

final class ErpHttpClientTest extends TestCase
{
    public function testSuccessfulAuthenticatedRequest(): void
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://erp.example.test/health', $url);
            self::assertContains('Authorization: Bearer secret', $options['headers']);

            return new MockResponse('{"status":"ok"}', ['http_code' => 200]);
        });

        $gateway = new ErpHttpClient($client, new ErpClientConfig('https://erp.example.test', 'secret'));

        self::assertSame(['status' => 'ok'], $gateway->get('/health'));
    }

    public function testServerErrorIsMappedToDomainException(): void
    {
        $client = new MockHttpClient(new MockResponse('{"error":"unavailable"}', ['http_code' => 503]));
        $gateway = new ErpHttpClient($client, new ErpClientConfig('https://erp.example.test', 'secret'));

        $this->expectException(ErpApiException::class);
        $gateway->get('/health');
    }

    public function testExternalAbsoluteUrlIsRejected(): void
    {
        $client = new MockHttpClient([]);
        $gateway = new ErpHttpClient($client, new ErpClientConfig('https://erp.example.test', 'secret'));

        $this->expectException(\InvalidArgumentException::class);
        $gateway->get('https://evil.example.test/');
    }
}
