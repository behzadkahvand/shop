<?php

namespace App\EventSubscriber\Order;

use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Service\Order\Apology\OrderCancellationApologyService;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderStatusChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private OrderCancellationApologyService $orderCancellationApologyService,
        private OrderWalletPaymentHandler $orderWalletPaymentHandler,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderStatusChanged::class => ['onOrderCanceled']
        ];
    }

    public function onOrderCanceled(OrderStatusChanged $event): void
    {
        if ($event->getNewStatus() !== OrderStatus::CANCELED) {
            return;
        }

        $this->handleWalletPayments($event);

        $this->handleApology($event);
    }

    private function handleApology(OrderStatusChanged $event): void
    {
        try {
            $this->orderCancellationApologyService->apologize(
                $event->getOrder()
            );
        } catch (Exception $exception) {
            $this->logger->critical(
                'Exception in OrderStatusChangedSubscriber',
                [
                    'message' => $exception->getMessage(),
                    'orderId' => $event->getOrder()->getId(),
                    'trace'   => $exception->getTrace()
                ]
            );
        }
    }

    private function handleWalletPayments(OrderStatusChanged $event): void
    {
        $this->orderWalletPaymentHandler->handle($event->getOrder(), TransferReason::ORDER_CANCELED);
    }
}
