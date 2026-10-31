<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Subscriber;
use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
final class PaymentStateSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus) {}
    public static function getSubscribedEvents(): array { return [StateMachineTransitionEvent::class => 'onStateTransition']; }
    public function onStateTransition(StateMachineTransitionEvent $event): void
    {
        if ($event->getEntityName() !== OrderTransactionDefinition::ENTITY_NAME || $event->getToPlace()->getTechnicalName() !== 'paid') return;
        $orderId = $event->getOrderId();
        if (!is_string($orderId) || $orderId === '') return;
        $this->messageBus->dispatch(new ExportOrderMessage($orderId, bin2hex(random_bytes(16))));
    }
}