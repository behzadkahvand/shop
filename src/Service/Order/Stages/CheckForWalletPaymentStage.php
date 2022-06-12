<?php

namespace App\Service\Order\Stages;

use App\Dictionary\TransferReason;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Wallet\OrderWalletPaymentService;
use App\Service\Order\Wallet\TransferRequestFactory;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

final class CheckForWalletPaymentStage implements TagAwarePipelineStageInterface
{
    private const MINIMUM_ONLINE_PAYABLE = 1000;

    public function __construct(
        private OrderWalletPaymentService $orderWalletPaymentService,
        private TransferRequestFactory $transferRequestFactory
    ) {
    }

    public function __invoke(AbstractPipelinePayload $payload): AbstractPipelinePayload
    {
        assert($payload instanceof CreateOrderPayload);

        $order    = $payload->getOrder();
        $customer = $order->getCustomer();

        if (!$customer->hasWallet()) {
            return $payload;
        }

        $document   = $order->getOrderDocument();
        $payable    = $document->getAmount();
        $userCredit = $customer->getWalletBalance();

        if (!$this->shouldUseWallet($payload, $userCredit)) {
            return $payload;
        }

        $order->setHasWalletPayment(true);

        $walletPayable = $this->calculateWalletPayable($payable, $userCredit);

        $transferRequest = $this->transferRequestFactory->make(
            $walletPayable,
            TransferReason::ORDER_PURCHASE,
            order: $order
        );
        $this->orderWalletPaymentService->withdraw($order, $transferRequest);

        return $payload;
    }

    public static function getPriority(): int
    {
        return -20;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }

    private function shouldUseWallet(CreateOrderPayload $payload, int $userCredit): bool
    {
        return $payload->useWallet() && $userCredit > 0;
    }

    // If user credit is less than order payable and the difference is below 1000toman
    // then 1000toman should be paid online and the rest should be withdrawn from wallet
    // because most of online gateways does not accept payments below 1000toman
    private function calculateWalletPayable(int $payable, int $userCredit): int
    {
        if ($userCredit >= $payable) {
            return $payable;
        }

        $gap = $payable - $userCredit;
        if ($gap >= self::MINIMUM_ONLINE_PAYABLE) {
            $payable = $userCredit;
        } else {
            $payable = $payable - self::MINIMUM_ONLINE_PAYABLE;
        }

        return $payable;
    }
}
