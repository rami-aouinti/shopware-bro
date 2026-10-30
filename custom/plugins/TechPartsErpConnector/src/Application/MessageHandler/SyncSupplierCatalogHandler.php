<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\MessageHandler;

use Shopware\Core\Framework\Context;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use TechPartsErpConnector\Application\Import\ImportJobTracker;
use TechPartsErpConnector\Application\Import\SupplierImportRowFactory;
use TechPartsErpConnector\Application\Import\SupplierImportService;
use TechPartsErpConnector\Application\Message\SyncSupplierCatalogMessage;

#[AsMessageHandler]
final readonly class SyncSupplierCatalogHandler
{
    public function __construct(
        private SupplierImportRowFactory $rowFactory,
        private SupplierImportService $importService,
        private ImportJobTracker $jobTracker,
    ) {
    }

    public function __invoke(SyncSupplierCatalogMessage $message): void
    {
        $rows = [];
        $factoryRejected = [];
        foreach ($message->rows as $index => $payload) {
            try {
                $rows[] = $this->rowFactory->fromArray($payload);
            } catch (\InvalidArgumentException $exception) {
                $factoryRejected[] = ['row' => $index + 1, 'error' => $exception->getMessage()];
            }
        }

        // Let infrastructure exceptions escape so Messenger can retry the message.
        $context = Context::createCLIContext();
        $result = $this->importService->import($rows, $context, $message->batchSize);
        $received = count($message->rows);
        $rejected = $result->rejected + count($factoryRejected);

        $summary = $factoryRejected === []
            ? null
            : mb_substr(json_encode($factoryRejected, JSON_THROW_ON_ERROR), 0, 4000);

        $this->jobTracker->finish(
            $message->jobId,
            $received,
            $result->accepted,
            $rejected,
            $context,
            $summary,
        );
    }
}
