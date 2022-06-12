<?php

namespace App\Service\Order\OrderStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;

abstract class AbstractOrderStatus
{
    private OrderShipmentStatusService $orderShipmentStatusService;

    public function __construct(OrderShipmentStatusService $orderShipmentStatusService)
    {
        $this->orderShipmentStatusService = $orderShipmentStatusService;
    }

    public function new(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function waitCustomer(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function callFailed(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function waitingForPay(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function confirmed(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function delivered(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function canceled(Order $order): void
    {
        $order->setStatus(OrderStatus::CANCELED);

        $this->changeShipmentStatuses($order, OrderShipmentStatus::CANCELED);
    }

    public function canceledSystem(Order $order): void
    {
        throw new InvalidOrderStatusTransitionException();
    }

    public function refund(Order $order): void
    {
        $order->setStatus(OrderStatus::REFUND);
    }

    abstract public function support(string $status): bool;

    abstract public function validTransitions(): array;

    protected function changeShipmentStatuses(Order $order, string $nextStatus): void
    {
        $order->getShipments()
              ->filter(fn(OrderShipment $orderShipment) => $this->shipmentStatusShouldBeChanged($orderShipment, $nextStatus))
              ->forAll(function (int $index, OrderShipment $orderShipment) use ($nextStatus) {
                  $this->orderShipmentStatusService->change($orderShipment, $nextStatus);

                  return true;
              });
    }

    private function shipmentStatusShouldBeChanged(OrderShipment $orderShipment, string $nextStatus): bool
    {
        $status = $orderShipment->getStatus();

        $invalidStatuses = [
            OrderShipmentStatus::CANCELED,
            OrderShipmentStatus::CANCELED_BY_CUSTOMER,
            OrderShipmentStatus::DELIVERED
        ];

        return $status !== $nextStatus && !in_array($status, $invalidStatuses);
    }
}
