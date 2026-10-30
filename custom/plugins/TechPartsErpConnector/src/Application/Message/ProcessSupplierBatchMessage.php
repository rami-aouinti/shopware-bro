<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Message;

final readonly class ProcessSupplierBatchMessage
{
    public function __construct(public string $batchId, public string $jobId)
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $batchId) || !preg_match('/^[a-f0-9]{32}$/', $jobId)) {
            throw new \InvalidArgumentException('Batch and job IDs must be 32-character hexadecimal IDs');
        }
    }
}
