<?php

namespace App\Messaging\Handlers\Command\OrderItem;

use App\Entity\OrderItem;
use App\Messaging\Messages\Command\OrderItem\UpdateOrderItemFinalPrice;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateOrderItemFinalPriceHandler implements MessageHandlerInterface
{
    public function __construct(
        protected EntityManagerInterface $manager,
        protected OrderItemRepository $orderItemRepository
    ) {
    }

    public function __invoke(UpdateOrderItemFinalPrice $message): void
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->find($message->getOrderItemId());

        $finalPrice = $orderItem->getGrandTotal() / $orderItem->getQuantity();
        $orderItem->setFinalPrice($finalPrice);

        $this->manager->flush();
    }
}
