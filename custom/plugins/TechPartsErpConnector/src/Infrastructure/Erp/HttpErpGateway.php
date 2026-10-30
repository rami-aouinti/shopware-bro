<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Erp;

use TechPartsErpConnector\Domain\Contract\ErpGateway;
use TechPartsErpConnector\Domain\Model\ErpHealth;

final readonly class HttpErpGateway implements ErpGateway
{
    public function __construct(private ErpHttpClient $client)
    {
    }

    public function health(): ErpHealth
    {
        try {
            $this->client->get('/health');

            return new ErpHealth(true, 'http-erp');
        } catch (ErpApiException) {
            return new ErpHealth(false, 'http-erp');
        }
    }
}
