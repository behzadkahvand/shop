<?php

namespace App\Service\Product\Colors;

use App\Messaging\Messages\Command\Product\AddColorsToProduct;
use Symfony\Component\Messenger\MessageBusInterface;

class AddColorsService
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function add(int $productId): void
    {
        $message = new AddColorsToProduct($productId);

        $this->messageBus->dispatch($message);
    }

    public function batchAdd(array $productIds): void
    {
        foreach ($productIds as $productId) {
            $this->add($productId);
        }
    }
}
