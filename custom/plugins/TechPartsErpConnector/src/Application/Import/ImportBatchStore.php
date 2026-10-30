<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Durable batches and atomic job finalization.
 *
 * All terminal transitions for one job lock its parent row first, so a job
 * cannot be marked complete while another batch transition is in flight.
 */
final readonly class ImportBatchStore
{
    public function __construct(private Connection $connection, private ImportJobTracker $jobs)
    {
    }

    /**
     * @param list<array{supplierCode:string,externalProductId:string,productId:?string}> $rows
     */
    public function add(string $jobId, int $index, array $rows): string
    {
        if ($rows === [] || $index < 0 || !Uuid::isValid($jobId)) {
            throw new \InvalidArgumentException('Invalid import batch');
        }

        $id = Uuid::randomHex();
        $this->connection->insert('tp_import_batch', [
            'id' => $id,
            'job_id' => $jobId,
            'batch_index' => $index,
            'rows_json' => json_encode($rows, JSON_THROW_ON_ERROR),
            'status' => 'pending',
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'),
        ]);

        return $id;
    }

    /** @return list<string> */
    public function pendingIds(string $jobId): array
    {
        if (!Uuid::isValid($jobId)) {
            throw new \InvalidArgumentException('Invalid job ID');
        }

        return $this->connection->fetchFirstColumn(
            "SELECT id FROM tp_import_batch WHERE job_id = :jobId AND status = 'pending' ORDER BY batch_index",
            ['jobId' => $jobId],
        );
    }

    /**
     * @param callable(list<array<string,mixed>>):array{accepted:int,rejected:int,errorSummary:?string} $processor
     */
    public function process(string $jobId, string $batchId, callable $processor): void
    {
        $this->connection->transactional(function (Connection $connection) use ($jobId, $batchId, $processor): void {
            $this->lockJob($connection, $jobId);
            $row = $connection->fetchAssociative(
                'SELECT rows_json, status FROM tp_import_batch WHERE id = :batchId AND job_id = :jobId FOR UPDATE',
                ['batchId' => $batchId, 'jobId' => $jobId],
            );
            if ($row === false) {
                throw new \RuntimeException('Unknown import batch');
            }
            if ($row['status'] !== 'pending') {
                return; // Repeated delivery: terminal batches are immutable.
            }

            $decoded = json_decode((string) $row['rows_json'], true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded) || !array_is_list($decoded)) {
                throw new \RuntimeException('Invalid stored import batch payload');
            }

            $result = $processor($decoded);
            $connection->update('tp_import_batch', [
                'status' => 'applied',
                'accepted' => $result['accepted'],
                'rejected' => $result['rejected'],
                'last_error' => $result['errorSummary'],
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'),
            ], ['id' => $batchId, 'job_id' => $jobId]);

            $this->finishWhenAllTerminal($connection, $jobId);
        });
    }

    public function failed(string $jobId, string $batchId, string $reason): void
    {
        $this->connection->transactional(function (Connection $connection) use ($jobId, $batchId, $reason): void {
            $this->lockJob($connection, $jobId);
            $row = $connection->fetchAssociative(
                'SELECT rows_json, status FROM tp_import_batch WHERE id = :batchId AND job_id = :jobId FOR UPDATE',
                ['batchId' => $batchId, 'jobId' => $jobId],
            );
            if ($row === false || $row['status'] !== 'pending') {
                return;
            }

            $rows = json_decode((string) $row['rows_json'], true, 512, JSON_THROW_ON_ERROR);
            $connection->update('tp_import_batch', [
                'status' => 'failed',
                'accepted' => 0,
                'rejected' => count($rows),
                'last_error' => mb_substr($reason, 0, 4000),
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'),
            ], ['id' => $batchId, 'job_id' => $jobId]);

            $this->finishWhenAllTerminal($connection, $jobId);
        });
    }

    private function lockJob(Connection $connection, string $jobId): void
    {
        if (!Uuid::isValid($jobId)) {
            throw new \InvalidArgumentException('Invalid job ID');
        }

        $found = $connection->fetchOne(
            'SELECT id FROM tp_import_job WHERE id = :id FOR UPDATE',
            ['id' => Uuid::fromHexToBytes($jobId)],
        );
        if ($found === false) {
            throw new \RuntimeException('Unknown import job');
        }
    }

    private function finishWhenAllTerminal(Connection $connection, string $jobId): void
    {
        $pending = (int) $connection->fetchOne(
            "SELECT COUNT(*) FROM tp_import_batch WHERE job_id = :jobId AND status = 'pending'",
            ['jobId' => $jobId],
        );
        if ($pending > 0) {
            return;
        }

        $counts = $connection->fetchAssociative(
            'SELECT COALESCE(SUM(JSON_LENGTH(rows_json)), 0) AS received,
                    COALESCE(SUM(accepted), 0) AS accepted,
                    COALESCE(SUM(rejected), 0) AS rejected
             FROM tp_import_batch WHERE job_id = :jobId',
            ['jobId' => $jobId],
        );

        $this->jobs->finish(
            $jobId,
            (int) $counts['received'],
            (int) $counts['accepted'],
            (int) $counts['rejected'],
            Context::createDefaultContext(),
        );
    }
}
