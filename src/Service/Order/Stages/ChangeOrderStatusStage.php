<?php

namespace App\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;
use DateTime;

class ChangeOrderStatusStage implements TagAwarePipelineStageInterface
{
    public function __construct(
        private CreateOrderStatusLogService $createOrderStatusLogService
    ) {
    }

    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        /**
         * @var Order $order
         */
        $order         = $payload->getOrder();
        $paymentMethod = $order->getPaymentMethod();

        if (
            $this->paymentMethodIsOffline($paymentMethod) ||
            0 === $order->getPayable()
        ) {
            $nextStatus = OrderStatus::WAIT_CUSTOMER;
        } else {
            $nextStatus = OrderStatus::WAITING_FOR_PAY;
        }

        $this->createOrderStatusLogService->perform(
            new CreateOrderStatusLogValueObject($order, $order->getStatus(), $nextStatus),
            false
        );

        $order->setStatus($nextStatus);

        if (
            $this->paymentMethodIsOnline($paymentMethod) &&
            $nextStatus === OrderStatus::WAIT_CUSTOMER
        ) {
            $order->setPaidAt(new DateTime());
        }

        return $payload;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    public static function getPriority(): int
    {
        return -30;
    }

    private function paymentMethodIsOnline(string $paymentMethod): bool
    {
        return OrderPaymentMethod::ONLINE === $paymentMethod;
    }

    private function paymentMethodIsOffline(string $paymentMethod): bool
    {
        return OrderPaymentMethod::OFFLINE === $paymentMethod;
    }
}
