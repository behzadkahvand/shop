<?php

namespace App\Service\Order\Survey\Listeners;

use App\Dictionary\OrderStatus;
use App\Messaging\Messages\Command\Order\SendOrderSurveySms;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SendSurveySmsOnDeliveringOrderListener implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $messenger)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            OrderStatusChanged::class => 'onOrderStatusChanged',
        ];
    }

    public function onOrderStatusChanged(OrderStatusChanged $event): void
    {
        if (OrderStatus::DELIVERED !== $event->getNewStatus()) {
            return;
        }

        $this->messenger->dispatch(async_message(new SendOrderSurveySms($event->getOrder()->getId())));
    }
}
