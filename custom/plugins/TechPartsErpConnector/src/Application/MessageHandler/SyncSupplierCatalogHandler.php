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
        $factoryErrors = [];
        foreach ($message->rows as $index => $payload) {
            try {
                $rows[] = $this->rowFactory->fromArray($payload);
            } catch (\InvalidArgumentException $exception) {
                $factoryErrors[] = [
                    'row' => $index + 1,
                    'error' => $exception->getMessage(),
                ];
            }
        }

        // Do not catch infrastructure exceptions here: Messenger must retry them.
        $result = $this->importService->import($rows, Context::createCLIContext(), $message->batchSize);
        $received = count($message->rows);
        $rejected = $result->rejected + count($factoryErrors);
        $accepted = $result->accepted;

        // Invalid payload rows are accounted for alongside domain validation errors.
        $this->jobTracker->finish(
            $message->jobId,
            $received,
            $accepted,
            $rejected,
            Context::createCLIContext(),
            $factoryErrors === [] ? null : json_encode($factoryErrors, JSON_THROW_ON_ERROR),
        );
    }
}
