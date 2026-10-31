<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\MessageHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use EnterpriseOrderIntegration\Service\ErpClient;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
final class ExportOrderHandler
{
    public function __construct(private readonly Connection $connection, private readonly EntityRepository $orderRepository, private readonly ErpClient $erpClient, private readonly LoggerInterface $logger) {}
    public function __invoke(ExportOrderMessage $message): void
    {
        $id = strtolower(str_replace('-', '', $message->orderId));
        if (!preg_match('/^[a-f0-9]{32}$/', $id)) throw new \InvalidArgumentException('Invalid Shopware order UUID.');
        $orderBytes = hex2bin($id);
        if ($orderBytes === false) throw new \InvalidArgumentException('Invalid Shopware order UUID.');
        $export = $this->ensureExportRecord($orderBytes, $message);
        if ($export['status'] === 'succeeded') {
            $this->logger->info('Duplicate export message ignored', ['orderId' => $message->orderId, 'correlationId' => $message->correlationId]);
            return;
        }
        $exportId = $export['id'];
        $claimed = $this->connection->executeStatement("UPDATE enterprise_order_export SET status = 'processing', attempts = attempts + 1, updated_at = NOW(3) WHERE id = :id AND status IN ('pending', 'failed')", ['id' => $exportId], ['id' => ParameterType::BINARY]);
        if ($claimed !== 1) {
            $this->logger->info('Export message skipped because another worker owns the record', [
                'orderId' => $message->orderId,
                'exportId' => bin2hex($exportId),
                'correlationId' => $message->correlationId,
            ]);
            return;
        }
        try {
            $criteria = new Criteria([$message->orderId]);
            $criteria->addAssociation('lineItems');
            $criteria->addAssociation('currency');
            $criteria->addAssociation('orderCustomer');
            $order = $this->orderRepository->search($criteria, Context::createDefaultContext())->first();
            if (!$order instanceof OrderEntity) throw new \RuntimeException('Shopware order was not found.');
            $response = $this->erpClient->exportOrder($this->mapOrder($order), $message->correlationId);
            $statusCode = $response->getStatusCode();
            $body = $response->toArray(false);
            if ($statusCode < 200 || $statusCode >= 300) throw new \RuntimeException(sprintf('ERP returned HTTP %d.', $statusCode));
            $reference = isset($body['reference']) && is_scalar($body['reference']) ? (string) $body['reference'] : null;
            $this->connection->executeStatement('UPDATE enterprise_order_export SET status = :status, erp_reference = :reference, last_error = NULL, next_attempt_at = NULL, updated_at = NOW(3) WHERE id = :id', ['status' => 'succeeded', 'reference' => $reference, 'id' => $exportId], ['id' => ParameterType::BINARY]);
            $this->logger->info('ERP order export succeeded', ['orderId' => $message->orderId, 'exportId' => bin2hex($exportId), 'correlationId' => $message->correlationId, 'httpStatus' => $statusCode]);
        } catch (\Throwable $exception) {
            $this->connection->executeStatement("UPDATE enterprise_order_export SET status = 'failed', last_error = :error, next_attempt_at = DATE_ADD(NOW(3), INTERVAL 5 MINUTE), updated_at = NOW(3) WHERE id = :id", ['error' => mb_substr($exception->getMessage(), 0, 1000), 'id' => $exportId], ['id' => ParameterType::BINARY]);
            $this->logger->error('ERP order export failed', ['orderId' => $message->orderId, 'exportId' => bin2hex($exportId), 'correlationId' => $message->correlationId, 'exceptionClass' => $exception::class, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
    private function ensureExportRecord(string $orderBytes, ExportOrderMessage $message): array
    {
        $this->connection->executeStatement('INSERT IGNORE INTO enterprise_order_export (id, order_id, status, attempts, correlation_id, created_at) VALUES (:id, :orderId, :status, 0, :correlationId, NOW(3))', ['id' => random_bytes(16), 'orderId' => $orderBytes, 'status' => 'pending', 'correlationId' => $message->correlationId], ['id' => ParameterType::BINARY, 'orderId' => ParameterType::BINARY]);
        $record = $this->connection->fetchAssociative('SELECT id, status FROM enterprise_order_export WHERE order_id = :orderId', ['orderId' => $orderBytes], ['orderId' => ParameterType::BINARY]);
        if (!is_array($record)) throw new \RuntimeException('Could not initialize ERP export record.');
        return ['id' => (string) $record['id'], 'status' => (string) $record['status']];
    }
    private function mapOrder(OrderEntity $order): array
    {
        $items = [];
        foreach ($order->getLineItems() ?? [] as $item) $items[] = ['id' => $item->getId(), 'productNumber' => $item->getPayload()['productNumber'] ?? null, 'label' => $item->getLabel(), 'quantity' => $item->getQuantity(), 'unitPrice' => $item->getUnitPrice(), 'totalPrice' => $item->getTotalPrice()];
        return ['source' => 'shopware', 'orderNumber' => $order->getOrderNumber(), 'orderId' => $order->getId(), 'orderDateTime' => $order->getOrderDateTime()?->format(DATE_ATOM), 'currency' => $order->getCurrency()?->getIsoCode(), 'amountTotal' => $order->getAmountTotal(), 'customerNumber' => $order->getOrderCustomer()?->getCustomerNumber(), 'lineItems' => $items];
    }
}