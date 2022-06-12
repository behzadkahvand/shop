<?php

namespace App\Service\Order\AddBalanceAmount;

use App\Messaging\Messages\Command\Order\AddBalanceAmountToOrder;
use Symfony\Component\Messenger\MessageBusInterface;

class AddBalanceAmountService
{
    public function __construct(protected MessageBusInterface $messageBus)
    {
    }

    public function addOne(int $orderId): void
    {
        $message = new AddBalanceAmountToOrder($orderId);

        $this->messageBus->dispatch($message);
    }

    public function addMany(array $orderIds): void
    {
        foreach ($orderIds as $orderId) {
            $this->addOne($orderId);
        }
    }
}
