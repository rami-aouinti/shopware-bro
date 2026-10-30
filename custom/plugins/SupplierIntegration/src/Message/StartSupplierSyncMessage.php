<?php declare(strict_types=1);

namespace SupplierIntegration\Message;

final class StartSupplierSyncMessage
{
    public function __construct(public readonly string $supplierId, public readonly string $syncRunId)
    {
    }
}
