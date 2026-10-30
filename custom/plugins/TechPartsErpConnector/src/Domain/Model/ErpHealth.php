<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Model;

final readonly class ErpHealth
{
    public function __construct(public bool $available, public string $provider)
    {
        if (trim($provider) === '') {
            throw new \InvalidArgumentException('Provider must not be empty');
        }
    }
}
