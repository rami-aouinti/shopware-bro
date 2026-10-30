<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Erp;

final class ErpApiException extends \RuntimeException
{
    public function __construct(string $message, public readonly ?int $httpStatus = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
