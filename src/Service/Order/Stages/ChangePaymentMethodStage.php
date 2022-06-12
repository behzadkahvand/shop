<?php

namespace App\Service\Order\Stages;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Dictionary\OrderPaymentMethod;
use App\Entity\Order;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class ChangePaymentMethodStage implements TagAwarePipelineStageInterface
{
    public function __construct(protected ConfigurationServiceInterface $configurationService)
    {
    }

    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        /**
         * @var Order $order
         */
        $order            = $payload->getOrder();
        $amount           = $order->getOrderDocument()->getAmount();
        $paymentMethod    = $order->getPaymentMethod();
        $newPaymentMethod = $paymentMethod;
        $payable          = $order->getPayable();

        if ($this->paymentMethodIsOnline($paymentMethod) && 50_000_000 < $payable) {
            $newPaymentMethod = OrderPaymentMethod::OFFLINE;
        }

        if (
            $this->paymentMethodIsOffline($paymentMethod) &&
            (
                $payable > 3_000_000 && $payable <= 50_000_000 ||
                !$payload->getCustomerAddress()->isCityExpress() ||
                1 < $order->getShipmentsCount()
            )
        ) {
            $newPaymentMethod = OrderPaymentMethod::ONLINE;
        }

        if ($this->paymentMethodIsCPG($paymentMethod) && $this->cpgGatewayIsNotAvailable()) {
            $newPaymentMethod = OrderPaymentMethod::ONLINE;
        }

        if ($this->paymentMethodIsHamrahCard($paymentMethod) && $this->hamrahCardGatewayIsNotAvailable()) {
            $newPaymentMethod = OrderPaymentMethod::ONLINE;
        }

        if (0 === $amount) {
            //When order amount is zero, order payable amount is as same and equals to zero!
            $newPaymentMethod = OrderPaymentMethod::OFFLINE;
        } elseif (0 === $payable) {
            //Order was paid with wallet, order amount is not zero!
            $newPaymentMethod = OrderPaymentMethod::ONLINE;
        }

        if ($paymentMethod !== $newPaymentMethod) {
            $payload->setPaymentMethod($newPaymentMethod);

            $order->setPaymentMethod($newPaymentMethod);
        }

        return $payload;
    }

    public static function getPriority(): int
    {
        return -25;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    private function paymentMethodIsOnline(string $paymentMethod): bool
    {
        return OrderPaymentMethod::ONLINE === $paymentMethod;
    }

    private function paymentMethodIsOffline(string $paymentMethod): bool
    {
        return OrderPaymentMethod::OFFLINE === $paymentMethod;
    }

    private function paymentMethodIsCPG(string $paymentMethod): bool
    {
        return OrderPaymentMethod::CPG === $paymentMethod;
    }

    private function paymentMethodIsHamrahCard(string $paymentMethod): bool
    {
        return OrderPaymentMethod::HAMRAH_CARD === $paymentMethod;
    }

    private function cpgGatewayIsNotAvailable(): bool
    {
        $availability = $this->configurationService->findByCode(ConfigurationCodeDictionary::CPG_GATEWAY_AVAILABILITY);

        return null !== $availability && $availability->getValue() == false;
    }

    private function hamrahCardGatewayIsNotAvailable(): bool
    {
        $availability = $this->configurationService->findByCode(ConfigurationCodeDictionary::HAMRAH_CARD_GATEWAY_AVAILABILITY);

        return null !== $availability && $availability->getValue() == false;
    }
}
