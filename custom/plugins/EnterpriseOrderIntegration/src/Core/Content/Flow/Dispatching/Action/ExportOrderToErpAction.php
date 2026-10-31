<?php declare(strict_types=1);

namespace EnterpriseOrderIntegration\Core\Content\Flow\Dispatching\Action;

use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\Aware\OrderTransactionAware;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Messenger\MessageBusInterface;

final class ExportOrderToErpAction extends FlowAction
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityRepository $orderTransactionRepository,
    ) {
    }

    public static function getName(): string
    {
        return 'action.enterprise_order.export_to_erp';
    }

    public function requirements(): array
    {
        return [OrderTransactionAware::class];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        if (!$flow->hasStore(OrderTransactionAware::ORDER_TRANSACTION_ID)) {
            throw new \RuntimeException('The Flow does not contain an order transaction ID.');
        }

        $transactionId = $flow->getStore(OrderTransactionAware::ORDER_TRANSACTION_ID);

        if (!is_string($transactionId) || $transactionId === '') {
            throw new \RuntimeException('Invalid order transaction ID.');
        }

        $transaction = $this->orderTransactionRepository
            ->search(new Criteria([$transactionId]), $flow->getContext())
            ->first();

        if ($transaction === null || !method_exists($transaction, 'getOrderId')) {
            throw new \RuntimeException('The order transaction could not be found.');
        }

        $orderId = $transaction->getOrderId();

        if (!is_string($orderId) || $orderId === '') {
            throw new \RuntimeException('The order transaction has no valid order ID.');
        }

        $this->messageBus->dispatch(
            new ExportOrderMessage($orderId, bin2hex(random_bytes(16))),
        );
    }
}
