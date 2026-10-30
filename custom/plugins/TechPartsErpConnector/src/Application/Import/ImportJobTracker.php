<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use TechPartsErpConnector\Domain\Model\ImportJobStatus;

final readonly class ImportJobTracker
{
    public function __construct(private EntityRepository $repository, private LoggerInterface $logger) {}

    public function start(string $source, Context $context): string
    {
        if (trim($source) === '') { throw new \InvalidArgumentException('Import source is required'); }
        $id = Uuid::randomHex();
        $this->repository->create([['id' => $id, 'source' => $source, 'status' => ImportJobStatus::Running->value, 'received' => 0, 'accepted' => 0, 'rejected' => 0, 'startedAt' => (new \DateTimeImmutable())->format(DATE_ATOM)]], $context);
        $this->logger->info('ERP import job started', ['jobId' => $id, 'source' => $source]);
        return $id;
    }

    public function finish(string $id, int $received, int $accepted, int $rejected, Context $context, ?string $errorSummary = null): void
    {
        if (!Uuid::isValid($id) || min($received, $accepted, $rejected) < 0 || $accepted + $rejected > $received) {
            throw new \InvalidArgumentException('Invalid import job completion data');
        }
        $status = $rejected === 0 ? ImportJobStatus::Succeeded : ($accepted > 0 ? ImportJobStatus::PartiallyFailed : ImportJobStatus::Failed);
        $this->repository->update([['id' => $id, 'status' => $status->value, 'received' => $received, 'accepted' => $accepted, 'rejected' => $rejected, 'errorSummary' => $errorSummary, 'finishedAt' => (new \DateTimeImmutable())->format(DATE_ATOM)]], $context);
        $this->logger->info('ERP import job finished', ['jobId' => $id, 'status' => $status->value, 'received' => $received, 'accepted' => $accepted, 'rejected' => $rejected]);
    }
}
