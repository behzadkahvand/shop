<?php

namespace App\Service\Order\OrderBalanceStatus;

use App\Dictionary\OrderBalanceStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Repository\OrderDocumentRepository;
use App\Repository\OrderRepository;
use App\Repository\RefundDocumentRepository;

class OrderBalanceStatusService
{
    protected OrderRepository $orderRepository;

    protected OrderDocumentRepository $orderDocumentRepository;

    protected RefundDocumentRepository $refundDocumentRepository;

    public function __construct(
        OrderRepository $orderRepository,
        OrderDocumentRepository $orderDocumentRepository,
        RefundDocumentRepository $refundDocumentRepository
    ) {
        $this->orderRepository          = $orderRepository;
        $this->orderDocumentRepository  = $orderDocumentRepository;
        $this->refundDocumentRepository = $refundDocumentRepository;
    }

    public function get(int $orderId): array
    {
        /**
         * @var Order $order
         */
        $order              = $this->orderRepository->find($orderId);
        $documentData       = $this->orderDocumentRepository->getOrderDocumentData($orderId);
        $refundDocumentData = $this->refundDocumentRepository->getOrderRefundDocumentsData($orderId);

        $balanceAmount = $documentData['totalTransactionAmounts'] - $refundDocumentData['totalRefundTransactionAmounts'];

        if ($order->getStatus() !== OrderStatus::CANCELED) {
            $balanceAmount -= $documentData['orderDocumentAmount'];
        }

        $balanceStatus = OrderBalanceStatus::BALANCE;

        if ($balanceAmount > 0) {
            $balanceStatus = OrderBalanceStatus::CREDITOR;
        } elseif ($balanceAmount < 0) {
            $balanceStatus = OrderBalanceStatus::DEBTOR;
        }

        return array_merge($documentData, $refundDocumentData, [
            'balanceAmount' => abs($balanceAmount),
            'balanceStatus' => $balanceStatus
        ]);
    }
}
