<?php

namespace App\Service\Order\Listeners;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Messaging\Messages\Command\Order\SendOrderAffiliatorPurchaseRequest;
use App\Service\Order\AutoConfirm\AutoConfirmOrderServiceInterface;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use App\Service\Payment\Events\PaymentSucceeded;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class AdvanceOrderStatusOnSuccessfulOnlinePaymentListener implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CreateOrderStatusLogService $orderStatusLogService,
        private AutoConfirmOrderServiceInterface $autoConfirmOrderService,
        private MessageBusInterface $messageBus
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSucceeded::class => ['onPaymentSucceeded', 1000],
        ];
    }

    public function onPaymentSucceeded(PaymentSucceeded $event): void
    {
        $order      = $event->getOrder();
        $fromStatus = $order->getStatus();

        if ($order->getPaymentMethod() === OrderPaymentMethod::OFFLINE) {
            return;
        }

        $this->manager->beginTransaction();

        try {
            $order->setStatus(OrderStatus::WAIT_CUSTOMER);
            $order->setPaidAt(new DateTime());

            $statusLogValueObject = new CreateOrderStatusLogValueObject(
                $order,
                $fromStatus,
                OrderStatus::WAIT_CUSTOMER
            );

            $this->orderStatusLogService->perform($statusLogValueObject, false);

            if ($this->autoConfirmOrderService->isConfirmable($order)) {
                $this->autoConfirmOrderService->confirm($order);
            }

            $this->manager->flush();
            $this->manager->commit();
        } catch (Exception $e) {
            $this->manager->close();
            $this->manager->rollback();

            throw $e;
        }

        if ($order->getAffiliator()) {
            $message = new SendOrderAffiliatorPurchaseRequest($order->getId());

            $this->messageBus->dispatch($message);
        }
    }
}
