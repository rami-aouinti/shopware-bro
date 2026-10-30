<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Model;

enum ImportJobStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case PartiallyFailed = 'partially_failed';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Succeeded, self::PartiallyFailed, self::Failed => true,
            default => false,
        };
    }
}
