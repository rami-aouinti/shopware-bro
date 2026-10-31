<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Application\Message;
final readonly class ExportOrderMessage
{
    public function __construct(public string $orderId, public string $correlationId) {}
}