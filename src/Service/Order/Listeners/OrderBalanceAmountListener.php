<?php

namespace App\Service\Order\Listeners;

use App\Events\Order\OrderBalanceAmountEvent;
use App\Service\Order\AddBalanceAmount\AddBalanceAmountService;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Service\Payment\Events\PaymentSucceeded;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class OrderBalanceAmountListener implements EventSubscriberInterface
{
    protected AddBalanceAmountService $addBalanceAmountService;

    public function __construct(AddBalanceAmountService $addBalanceAmountService)
    {
        $this->addBalanceAmountService = $addBalanceAmountService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderBalanceAmountEvent::class => ['onOrderBalanceAmount'],
        ];
    }

    public function onOrderBalanceAmount(OrderBalanceAmountEvent $event): void
    {
        $this->addBalanceAmountService->addOne($event->getOrderId());
    }
}
