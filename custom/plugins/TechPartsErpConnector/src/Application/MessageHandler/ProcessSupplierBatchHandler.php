<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\MessageHandler;

use Shopware\Core\Framework\Context;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use TechPartsErpConnector\Application\Import\ImportBatchStore;
use TechPartsErpConnector\Application\Import\SupplierImportRowFactory;
use TechPartsErpConnector\Application\Import\SupplierImportService;
use TechPartsErpConnector\Application\Message\ProcessSupplierBatchMessage;

#[AsMessageHandler]
final readonly class ProcessSupplierBatchHandler
{
    public function __construct(
        private ImportBatchStore $batches,
        private SupplierImportRowFactory $rowFactory,
        private SupplierImportService $importer,
    ) {
    }

    public function __invoke(ProcessSupplierBatchMessage $message): void
    {
        $this->batches->process($message->jobId, $message->batchId, function (array $payload): array {
            $rows = [];
            $invalid = [];
            foreach ($payload as $position => $record) {
                try {
                    $rows[] = $this->rowFactory->fromArray($record);
                } catch (\InvalidArgumentException $exception) {
                    $invalid[] = ['row' => $position + 1, 'error' => $exception->getMessage()];
                }
            }

            $result = $this->importer->import($rows, Context::createCLIContext());
            $allErrors = [...$invalid, ...$result->errors];

            return [
                'accepted' => $result->accepted,
                'rejected' => $result->rejected + count($invalid),
                'errorSummary' => $allErrors === [] ? null : json_encode(
                    array_slice($allErrors, 0, 20),
                    JSON_THROW_ON_ERROR
                ),
            ];
        });
    }
}
