<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Model;

final readonly class ImportJob
{
    public function __construct(
        public string $id,
        public ImportJobStatus $status,
        public int $received,
        public int $accepted,
        public int $rejected,
        public ?string $errorSummary = null,
    ) {
        if ($id === '' || min($received, $accepted, $rejected) < 0) {
            throw new \InvalidArgumentException('Invalid import job data');
        }
        if ($accepted + $rejected > $received) {
            throw new \InvalidArgumentException('Accepted and rejected rows exceed received rows');
        }
    }
}
