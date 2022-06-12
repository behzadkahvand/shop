<?php

namespace App\Service\Order\UpdateOrderItems;

use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderRepository;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\UpdateOrderItems\Event\OrderItemsUpdated;
use App\Service\Order\UpdateOrderItems\Event\OrderItemUpdated;
use App\Service\Order\UpdateOrderItems\Exceptions\InvalidOrderException;
use App\Service\Order\UpdateOrderItems\Exceptions\InvalidOrderItemIdException;
use App\Service\Order\UpdateOrderItems\Exceptions\OrderDocumentNotFoundException;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderItemLog\OrderItemLogCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateOrderItemsService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected EntityManagerInterface $entityManager,
        protected OrderItemLogCreator $orderItemLogCreator,
        protected EventDispatcherInterface $eventDispatcher,
        protected RecalculateOrderDocument $recalculateOrderDocument,
        protected OrderWalletPaymentHandler $orderWalletPaymentHandler
    ) {
    }

    /**
     * @throws InvalidOrderException
     * @throws InvalidOrderItemIdException
     * @throws OrderDocumentNotFoundException
     */
    public function perform(int $orderId, array $items, Admin $admin): Order
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $order = $this->orderRepository->findOrdersWithStatusAndId($orderId, $validStatuses);

        if (!$order) {
            throw new InvalidOrderException();
        }

        $orderItems = collect($order->getOrderItems())->keyBy(fn(OrderItem $oi) => $oi->getId())->all();

        $skippedItemsCount = 0;
        $updatedOrderItems = [];
        foreach ($items as $item) {
            $id = $item['id'];

            if (!isset($orderItems[$id])) {
                throw new InvalidOrderItemIdException();
            }

            /** @var OrderItem $orderItem */
            $orderItem    = $orderItems[$id];
            $currentCount = $orderItem->getQuantity();
            $newCount     = $item['quantity'] ?? $currentCount;
            $currentPrice = $orderItem->getPrice();
            $newPrice     = $item['price'] ?? $orderItem->getFinalPrice();

            if ($newCount === $currentCount && $newPrice === $currentPrice) {
                $skippedItemsCount++;

                continue;
            }

            $newSubTotal   = $newCount * $currentPrice;
            $newGrandTotal = $newCount * $newPrice;
            $oldGrandTotal = $orderItem->getGrandTotal();

            $orderItem->setQuantity($newCount)
                      ->setSubtotal($newSubTotal)
                      ->setGrandTotal($newGrandTotal);

            if ($currentCount !== $newCount) {
                $this->orderItemLogCreator->create($orderItem, $currentCount, $newCount, $admin);

                $this->updateInventoryStock($orderItem, $currentCount, $newCount);
            }

            if ($oldGrandTotal !== $newGrandTotal) {
                $this->eventDispatcher->dispatch(new OrderItemUpdated($orderItem, $oldGrandTotal));
            }

            $updatedOrderItems[] = $orderItem;
        }

        if ($skippedItemsCount === count($orderItems)) {
            return $order;
        }

        $this->recalculateOrderDocument->perform($order);

        $this->eventDispatcher->dispatch(new OrderItemsUpdated($order, $updatedOrderItems, $admin));

        $this->orderWalletPaymentHandler->handle($order, TransferReason::UPDATE_ORDER_ITEM);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

        return $order;
    }

    private function updateInventoryStock(OrderItem $orderItem, int $currentCount, int $newCount): void
    {
        $orderStatus = $orderItem->getOrder()->getStatus();
        $changeOfQuantity = $newCount - $currentCount;

        if (0 > $changeOfQuantity && OrderStatus::WAIT_CUSTOMER === $orderStatus) {
            $orderItem->getInventory()->increaseStockCount(abs($changeOfQuantity));
        }

        if (0 < $changeOfQuantity) {
            $orderItem->getInventory()->decreaseStockCount($changeOfQuantity);
        }
    }
}
