<?php

namespace App\EventSubscriber\Order\ReturnRequest;

use App\Dictionary\TransferReason;
use App\Entity\ReturnRequestItem;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowRefundSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected RecalculateOrderDocument $recalculateOrderDocument,
        protected OrderWalletPaymentHandler $walletPaymentHandler,
        protected EventDispatcherInterface $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.return_request.completed.' . ReturnRequestTransition::REFUND => 'onRefundComplete',
        ];
    }


    public function onRefundComplete(Event $event): void
    {
        $returnRequestItem = $event->getSubject();
        assert($returnRequestItem instanceof ReturnRequestItem);
        $order = $returnRequestItem->getOrderItem()->getOrder();

        $this->recalculateOrderDocument->perform($order);
        $this->walletPaymentHandler->handle($order, TransferReason::ORDER_REFUND);
        $this->dispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));
    }
}
