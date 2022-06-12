<?php

namespace App\Service\Order\UpdateOrderPaymentMethod;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Exceptions\Order\InvalidOrderStatusException;
use App\Repository\OrderRepository;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\InvalidOrderException;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\InvalidOrderPaymentMethodException;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\OfflinePaymentMethodException;
use Doctrine\ORM\EntityManagerInterface;

class UpdateOrderPaymentMethodService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param int $orderId
     * @param string $paymentMethod
     * @return Order
     * @throws InvalidOrderException
     * @throws InvalidOrderPaymentMethodException
     * @throws OfflinePaymentMethodException
     * @throws InvalidOrderStatusException
     */
    public function perform(int $orderId, string $paymentMethod): Order
    {
        $orderStatus = $this->getOrderStatus($paymentMethod);

        $order = $this->orderRepository->findUnpaidOrderWithId($orderId);

        if (!$order) {
            throw new InvalidOrderException();
        }

        if (OrderPaymentMethod::OFFLINE === $paymentMethod && (true === $order->isPaid() || $order->getShipmentsCount() > 1)) {
            throw new OfflinePaymentMethodException();
        }

        $order->setPaymentMethod($paymentMethod)
              ->setStatus($orderStatus);

        $this->entityManager->flush();

        return $order;
    }

    /**
     * @param string $paymentMethod
     * @return string
     * @throws InvalidOrderPaymentMethodException
     */
    private function getOrderStatus(string $paymentMethod): string
    {
        if (in_array($paymentMethod, $this->getOnlinePaymentMethods(), true)) {
            return OrderStatus::WAITING_FOR_PAY;
        }

        if ($paymentMethod === OrderPaymentMethod::OFFLINE) {
            return OrderStatus::WAIT_CUSTOMER;
        }

        throw new InvalidOrderPaymentMethodException();
    }

    private function getOnlinePaymentMethods(): array
    {
        return [OrderPaymentMethod::ONLINE, OrderPaymentMethod::CPG, OrderPaymentMethod::HAMRAH_CARD];
    }
}
