<?php

namespace App\Service\Order\DeleteOrderItem;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\TransferReason;
use App\Entity\Admin;
use App\Entity\OrderItem;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Exceptions\UnremovableEntityException;
use App\Repository\OrderItemRepository;
use App\Service\Order\DeleteOrderItem\Event\OrderItemRemoved;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\UpdateOrderItems\Exceptions\OrderDocumentNotFoundException;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DeleteOrderItemService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected OrderItemRepository $orderItemRepository,
        protected SellerOrderItemStatusService $sellerOrderItemStatusService,
        protected EventDispatcherInterface $eventDispatcher,
        protected RecalculateOrderDocument $recalculateOrderDocument,
        protected OrderWalletPaymentHandler $walletPaymentHandler
    ) {
    }

    /**
     * @throws OrderDocumentNotFoundException
     * @throws UnremovableEntityException
     * @throws Throwable
     */
    public function perform(int $orderItemId, Admin $admin): void
    {
        $this->entityManager->beginTransaction();

        try {
            $orderItem = $this->orderItemRepository->findOrFail($orderItemId);
            $this->validateDeleteOperation($orderItem);

            $this->entityManager->lock($orderItem, LockMode::PESSIMISTIC_READ);

            $order = $orderItem->getOrder();
            $this->entityManager->lock($order, LockMode::PESSIMISTIC_READ);

            if ($order->getOrderItems()->count() > 1 && OrderStatus::WAIT_CUSTOMER === $order->getStatus()) {
                $orderItem->releaseReservedStock();
            }

            $this->sellerOrderItemStatusService->change(
                $orderItem->getSellerOrderItem(),
                SellerOrderItemStatus::CANCELED_BY_USER
            );

            $order->removeOrderItem($orderItem);
            $orderItem->getOrderShipment()->removeOrderItem($orderItem);

            $this->recalculateOrderDocument->perform($order);

            $this->entityManager->remove($orderItem);

            //@TODO Remove duplicate recalculate order document, Recalculate order document after event dispatched!
            $this->eventDispatcher->dispatch(new OrderItemRemoved($orderItem, $order, $admin));

            $this->walletPaymentHandler->handle($order, TransferReason::ORDER_REFUND);

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new OrderBalanceAmountEvent($order->getId()));

            $this->entityManager->commit();
        } catch (Throwable $exception) {
            $this->entityManager->close();
            $this->entityManager->rollback();

            throw $exception;
        }
    }

    /**
     * @param   OrderItem  $orderItem
     *
     * @throws UnremovableEntityException
     */
    private function validateDeleteOperation(OrderItem $orderItem): void
    {
        if ($orderItem->getOrderShipment()->hasOnlyOneDistinctItem()) {
            throw new UnremovableEntityException(
                'Order item can not be deleted because its shipment has only one item.'
            );
        }
    }
}
