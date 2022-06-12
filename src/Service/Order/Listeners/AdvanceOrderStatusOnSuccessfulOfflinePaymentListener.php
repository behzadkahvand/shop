<?php

namespace App\Service\Order\Listeners;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Payment\Events\PaymentSucceeded;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AdvanceOrderStatusOnSuccessfulOfflinePaymentListener implements EventSubscriberInterface
{
    private EntityManagerInterface $manager;

    private OrderShipmentStatusService $orderShipmentStatusService;

    public function __construct(EntityManagerInterface $manager, OrderShipmentStatusService $orderShipmentStatusService)
    {
        $this->manager = $manager;
        $this->orderShipmentStatusService = $orderShipmentStatusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSucceeded::class => ['onPaymentSucceeded', 1000],
        ];
    }

    public function onPaymentSucceeded(PaymentSucceeded $event): void
    {
        $order = $event->getOrder();
        $orderShipment = $event->getTransaction()->getOrderShipment();

        if ($order->getPaymentMethod() !== OrderPaymentMethod::OFFLINE) {
            return;
        }

        $this->manager->beginTransaction();

        try {
            $this->orderShipmentStatusService->change($orderShipment, OrderShipmentStatus::DELIVERED);

            if ($order->getStatus() === OrderStatus::DELIVERED) {
                $order->setPaidAt(new DateTime());
            }

            $this->manager->flush();
            $this->manager->commit();
        } catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollback();

            throw $e;
        }
    }
}
