<?php

namespace App\Service\Order\Listeners;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\TransactionStatus;
use App\Entity\Order;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Payment\Events\CODPaymentSucceeded;
use App\Service\Payment\Events\PaymentSucceeded;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FailOrderPendingTransactionsForOfflinePaymentListener implements EventSubscriberInterface
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
            PaymentSucceeded::class => ['onPaymentSucceeded', 900],
            CODPaymentSucceeded::class => ['onCODPaymentSucceeded', 900],
        ];
    }

    public function onPaymentSucceeded(PaymentSucceeded $event): void
    {
        $order = $event->getOrder();

        $this->failOrderPendingTransactions($order);
    }

    public function onCODPaymentSucceeded(CODPaymentSucceeded $event): void
    {
        $order = $event->getTransaction()->getDocument()->getOrder();

        $this->failOrderPendingTransactions($order);
    }

    private function failOrderPendingTransactions(Order $order): void
    {
        if ($order->getPaymentMethod() !== OrderPaymentMethod::OFFLINE) {
            return;
        }

        if ($order->getPaidAt() !== null) {
            $transactions = $order->getOrderDocument()->getTransactions();

            foreach ($transactions as $transaction) {
                if ($transaction->getStatus() === TransactionStatus::PENDING) {
                    $transaction->setStatus(TransactionStatus::FAILED);
                }
            }
        }

        $this->manager->flush();
    }
}
