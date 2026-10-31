<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Subscriber;
use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
final class PaymentStateSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus, private readonly EntityRepository $orderTransactionRepository) {}
    public static function getSubscribedEvents(): array { return [StateMachineTransitionEvent::class => 'onStateTransition']; }
    public function onStateTransition(StateMachineTransitionEvent $event): void
    {
        if ($event->getEntityName() !== OrderTransactionDefinition::ENTITY_NAME || $event->getToPlace()->getTechnicalName() !== 'paid') return;
        $transactionId = $event->getEntityId();
        if (!is_string($transactionId) || $transactionId === '') return;
        $transaction = $this->orderTransactionRepository->search(new Criteria([$transactionId]), $event->getContext())->first();
        if ($transaction === null || !method_exists($transaction, 'getOrderId')) return;
        $orderId = $transaction->getOrderId();
        if (!is_string($orderId) || $orderId === '') return;
        $this->messageBus->dispatch(new ExportOrderMessage($orderId, bin2hex(random_bytes(16))));
    }
}