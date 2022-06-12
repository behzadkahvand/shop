<?php

namespace App\Service\Product\BuyBox;

use App\Messaging\Messages\Command\Product\AddBuyBoxToProduct;
use Symfony\Component\Messenger\MessageBusInterface;

class AddBuyBoxService
{
    public function __construct(protected MessageBusInterface $messageBus)
    {
    }

    public function addOne(int $productId): void
    {
        $message = new AddBuyBoxToProduct($productId);

        $this->messageBus->dispatch($message);
    }

    public function addMany(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->addOne($productId);
        }
    }
}
