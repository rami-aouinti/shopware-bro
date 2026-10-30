<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Erp;

final readonly class ErpClientConfig
{
    public function __construct(public string $baseUrl, public string $apiToken, public int $timeoutSeconds = 5)
    {
        if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false || !in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('ERP base URL must be an absolute HTTP(S) URL');
        }
        if ($timeoutSeconds < 1 || $timeoutSeconds > 60) {
            throw new \InvalidArgumentException('ERP timeout must be between 1 and 60 seconds');
        }
    }
}
