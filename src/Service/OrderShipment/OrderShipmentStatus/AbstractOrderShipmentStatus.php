<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;

abstract class AbstractOrderShipmentStatus
{
    public function __construct(
        private OrderStatusService $orderStatusService,
        private SellerOrderItemStatusService $sellerOrderItemStatusService,
        private RecalculateOrderDocument $recalculateOrderDocument
    ) {
    }

    public function new(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function waitingForSupply(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function preparing(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function prepared(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function packaged(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function waitingForSend(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function sent(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function delivered(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function canceled(OrderShipment $orderShipment): void
    {
        $orderShipment->setStatus(OrderShipmentStatus::CANCELED);

        $this->changeSellerOrderItemsStatus($orderShipment, SellerOrderItemStatus::CANCELED_BY_USER);

        $order = $orderShipment->getOrder();

        if ($this->orderShouldBeCanceled($order)) {
            $this->changeOrderStatus($order, OrderStatus::CANCELED);
        } elseif ($this->orderShouldBeDelivered($order)) {
            $this->changeOrderStatus($order, OrderStatus::DELIVERED);
        }

        $this->recalculateOrderDocument($order);
    }

    public function warehouse(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function afterSales(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function returning(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function returned(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function thirdPartyLogistics(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function customerAbsence(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function canceledByCustomer(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    public function noSend(OrderShipment $orderShipment): void
    {
        throw new InvalidOrderShipmentStatusTransitionException();
    }

    abstract public function validTransitions(): array;

    abstract public function support(string $status): bool;

    protected function changeOrderStatus(Order $order, string $orderStatus)
    {
        $this->orderStatusService->change($order, $orderStatus);
    }

    protected function changeSellerOrderItemsStatus(OrderShipment $orderShipment, string $nextStatus)
    {
        $orderShipment->getOrderItems()
                      ->map(fn(OrderItem $item) => $item->getSellerOrderItem())
                      ->filter(fn(SellerOrderItem $item) => $item->getStatus() !== $nextStatus)
                      ->forAll(function (int $index, SellerOrderItem $sellerOrderItem) use ($nextStatus) {
                          $this->sellerOrderItemStatusService->change($sellerOrderItem, $nextStatus);

                          return true;
                      });
    }

    private function orderShouldBeCanceled(Order $order): bool
    {
        if (in_array($order->getStatus(), [OrderStatus::CANCELED, OrderStatus::CANCELED_SYSTEM], true)) {
            return false;
        }

        return collect($order->getShipments())->every(function (OrderShipment $orderShipment) {
            return OrderShipmentStatus::CANCELED === $orderShipment->getStatus();
        });
    }

    protected function orderShouldBeDelivered(Order $order): bool
    {
        $shipments = collect($order->getShipments())
            ->filter(fn(OrderShipment $shipment) => !$shipment->isCanceled() && !$shipment->isCanceledByCustomer());

        return $shipments->isNotEmpty() && $shipments->every(fn(OrderShipment $shipment) => $shipment->isDelivered());
    }

    protected function recalculateOrderDocument(Order $order): void
    {
        $this->recalculateOrderDocument->perform($order);
    }
}
