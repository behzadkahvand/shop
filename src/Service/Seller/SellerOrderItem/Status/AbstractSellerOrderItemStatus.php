<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\Exceptions\InvalidSellerOrderItemStatusTransitionException;

/**
 * Class AbstractSellerOrderItemStatus
 */
abstract class AbstractSellerOrderItemStatus
{
    protected OrderShipmentStatusService $orderShipmentStatusService;

    /**
     * AbstractSellerOrderItemStatus constructor.
     *
     * @param OrderShipmentStatusService $orderShipmentStatusService
     */
    public function __construct(OrderShipmentStatusService $orderShipmentStatusService)
    {
        $this->orderShipmentStatusService = $orderShipmentStatusService;
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function waitingForSend(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::WAITING_FOR_SEND);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function sentBySeller(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::SENT_BY_SELLER);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function received(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::RECEIVED);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function fulfilling(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::FULFILLING);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function missed(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::MISSED);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function damaged(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::DAMAGED);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function storaged(SellerOrderItem $sellerOrderItem): void
    {
        $this->throwInvalidStatusTransitionException($sellerOrderItem, __FUNCTION__);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     *
     * @throws InvalidOrderShipmentStatusException
     */
    public function canceledByUser(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::CANCELED_BY_USER);

        $orderShipment = $sellerOrderItem->getOrderItem()->getOrderShipment();

        if ($orderShipment->isCanceled()) {
            return;
        }

        if ($this->orderShipmentShouldBeCanceled($orderShipment)) {
            $this->orderShipmentStatusService->change($orderShipment, OrderShipmentStatus::CANCELED);

            return;
        }

        if ($this->isShipmentFullyStoraged($orderShipment)) {
            $this->orderShipmentStatusService->change($orderShipment, OrderShipmentStatus::WAREHOUSE);
        }
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function canceledBySeller(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::CANCELED_BY_SELLER);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function returning(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::RETURNING);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function returned(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::RETURNED);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function sentToCustomer(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::SENT_TO_CUSTOMER);
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     */
    public function delivered(SellerOrderItem $sellerOrderItem): void
    {
        $sellerOrderItem->setStatus(SellerOrderItemStatus::DELIVERED);
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    public function support(string $status): bool
    {
        return $this->getName() === $status;
    }

    /**
     * @return string
     */
    abstract protected function getName(): string;

    /**
     * @param OrderShipment $orderShipment
     *
     * @return bool
     */
    protected function isShipmentFullyStoraged(OrderShipment $orderShipment): bool
    {
        $orderItems = collect($orderShipment->getOrderItems())->filter(
            fn (OrderItem $oi) => !$oi->getSellerOrderItem()->isRejected()
        );

        return $orderItems->isNotEmpty() && $orderItems->every(
            fn (OrderItem $oi) => $oi->getSellerOrderItem()->isStoraged()
        );
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     * @param string $toStatus
     *
     * @throws InvalidSellerOrderItemStatusTransitionException
     */
    protected function throwInvalidStatusTransitionException(SellerOrderItem $sellerOrderItem, string $toStatus): void
    {
        $fromStatus = $sellerOrderItem->getStatus();

        throw new InvalidSellerOrderItemStatusTransitionException($fromStatus, strtoupper(snake_case($toStatus)));
    }

    /**
     * @param OrderShipment $orderShipment
     *
     * @return bool
     */
    private function orderShipmentShouldBeCanceled(OrderShipment $orderShipment): bool
    {
        return $orderShipment->getOrderItemsCount() === 1;
    }
}
