<?php

namespace App\Service\Order\Wallet;

use App\Entity\Order;

class OrderWalletPaymentHandler
{
    public function __construct(private OrderWalletPaymentService $walletPaymentService, private TransferRequestFactory $transferRequestFactory)
    {
    }

    public function handle(Order $order, string $reason): void
    {
        if (!$order->hasWalletPayment()) {
            return;
        }
        $payable = $order->getPayable();

        if ($payable < 0) {
            $walletRefundable = $this->calculateWalletRefundable($order, abs($payable));

            $transferRequest = $this->transferRequestFactory->make(
                $walletRefundable,
                $reason,
                order: $order
            );
            $this->walletPaymentService->deposit($order, $transferRequest);
        }

        if ($payable > 0) {
            $walletPayable = $this->calculateWalletPayable($order, $payable);

            $transferRequest = $this->transferRequestFactory->make(
                $walletPayable,
                $reason,
                order: $order
            );
            $this->walletPaymentService->withdraw($order, $transferRequest);
        }
    }

    protected function calculateWalletRefundable(Order $order, int $payable): int
    {
        $totalWalletPayments = $order->calculateWalletPayments();
        $totalWalletRefunds = $order->calculateWalletRefunds();
        $walletRefundable = $totalWalletPayments - $totalWalletRefunds;

        if ($payable > $walletRefundable) {
            $payable = $walletRefundable;
        }

        return $payable;
    }

    protected function calculateWalletPayable(Order $order, int $payable): int
    {
        $credit = $order->getCustomer()->getWalletBalance();

        if ($payable > $credit) {
            $payable = $credit;
        }

        return $payable;
    }
}
