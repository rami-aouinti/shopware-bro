<?php declare(strict_types=1);

namespace SupplierIntegration\MessageHandler;

use Shopware\Core\Framework\Context;
use SupplierIntegration\Message\StartSupplierSyncMessage;
use SupplierIntegration\Service\OfferImportService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StartSupplierSyncHandler
{
    public function __construct(private readonly OfferImportService $importService)
    {
    }

    public function __invoke(StartSupplierSyncMessage $message): void
    {
        $this->importService->import($message->supplierId, $message->syncRunId, Context::createCLIContext());
    }
}
