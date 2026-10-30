<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Erp;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use TechPartsErpConnector\Domain\Contract\ErpGateway;
use TechPartsErpConnector\Domain\Model\ErpHealth;

final readonly class HttpErpGateway implements ErpGateway
{
    public function __construct(private ErpHttpClient $client, private SystemConfigService $config)
    {
    }

    #[\Override]
    public function health(): ErpHealth
    {
        if ($this->config->get('TechPartsErpConnector.config.enabled') === false) {
            return new ErpHealth(false, 'disabled');
        }

        try {
            $this->client->get('/health');

            return new ErpHealth(true, 'http-erp');
        } catch (ErpApiException) {
            return new ErpHealth(false, 'http-erp');
        }
    }
}
