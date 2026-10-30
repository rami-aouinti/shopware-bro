<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Messenger;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use TechPartsErpConnector\Application\Import\ImportJobTracker;
use TechPartsErpConnector\Application\Message\SyncSupplierCatalogMessage;

/**
 * Closes import jobs only after Messenger has exhausted its retry strategy.
 * Intermediate failures remain retryable and must not prematurely mark the job failed.
 */
final readonly class ImportFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ImportJobTracker $jobTracker,
        private LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [WorkerMessageFailedEvent::class => 'onMessageFailed'];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof SyncSupplierCatalogMessage) {
            return;
        }

        $exception = $event->getThrowable();

        try {
            $this->jobTracker->fail(
                $message->jobId,
                $exception::class . ': ' . $exception->getMessage(),
                Context::createCLIContext(),
            );
        } catch (\Throwable $trackingException) {
            $this->logger->critical('Could not mark exhausted supplier import as failed', [
                'jobId' => $message->jobId,
                'originalError' => $exception->getMessage(),
                'trackingError' => $trackingException->getMessage(),
            ]);
        }
    }
}
