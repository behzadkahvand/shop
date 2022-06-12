<?php

namespace App\Service\Order\AutoConfirm;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Order\OrderIsNotConfirmableException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;

/**
 * Class AutoConfirmOrderService
 */
class AutoConfirmOrderService implements AutoConfirmOrderServiceInterface
{
    private CreateOrderStatusLogService $createOrderStatusLogService;
    private OrderRepository $orderRepository;
    private OrderShipmentStatusService $orderShipmentStatusService;
    private bool $isOrderAddressConfirmed;

    private array $cache = [];

    /**
     * AutoConfirmOrderService constructor.
     *
     * @param CreateOrderStatusLogService $createOrderStatusLogService
     * @param OrderRepository $orderRepository
     * @param OrderShipmentStatusService $orderShipmentStatusService
     */
    public function __construct(
        CreateOrderStatusLogService $createOrderStatusLogService,
        OrderRepository $orderRepository,
        OrderShipmentStatusService $orderShipmentStatusService
    ) {
        $this->createOrderStatusLogService = $createOrderStatusLogService;
        $this->orderRepository             = $orderRepository;
        $this->orderShipmentStatusService  = $orderShipmentStatusService;
    }

    /**
     * @inheritDoc
     */
    public function isConfirmable(Order $order): bool
    {
        $identifier = $order->getIdentifier();

        if (isset($this->cache[$identifier])) {
            return $this->cache[$identifier];
        }

        $result = OrderStatus::CONFIRMED !== $order->getStatus()
               && OrderStatus::CONFIRMED === $this->getNewStatus($order);

        return $this->cache[$identifier] = $result;
    }

    /**
     * @param Order $order
     */
    public function confirm(Order $order): void
    {
        if (!$this->isConfirmable($order)) {
            throw new OrderIsNotConfirmableException($order);
        }

        $currentStatus = $order->getStatus();

        $order->setStatus(OrderStatus::CONFIRMED);

        $this->createOrderStatusLogService->perform(
            new CreateOrderStatusLogValueObject($order, $currentStatus, OrderStatus::CONFIRMED),
            false
        );

        foreach ($order->getShipments() as $orderShipment) {
            $this->orderShipmentStatusService->change($orderShipment, OrderShipmentStatus::WAITING_FOR_SUPPLY);
        }
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    private function getNewStatus(Order $order): string
    {
        $isOffline = OrderPaymentMethod::OFFLINE === $order->getPaymentMethod();

        // order just created
        if ($isOffline) {
            return $this->orderAddressIsConfirmed($order) ? OrderStatus::CONFIRMED : OrderStatus::WAIT_CUSTOMER;
        }

        $isWaitCustomer = OrderStatus::WAIT_CUSTOMER === $order->getStatus();

        // payment just verified
        if ($isWaitCustomer && $order->isPaid() && $this->orderAddressIsConfirmed($order)) {
            return OrderStatus::CONFIRMED;
        }

        return OrderStatus::WAITING_FOR_PAY;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    private function orderAddressIsConfirmed(Order $order): bool
    {
        if (isset($this->isOrderAddressConfirmed)) {
            return $this->isOrderAddressConfirmed;
        }

        $point    = $order->getOrderAddress()->getCoordinates();
        $customer = $order->getCustomer();

        $isOrderAddressConfirmed = 0 < count($this->orderRepository->successCustomerOrdersByPoint($point, $customer));

        return $this->isOrderAddressConfirmed = $isOrderAddressConfirmed;
    }
}
