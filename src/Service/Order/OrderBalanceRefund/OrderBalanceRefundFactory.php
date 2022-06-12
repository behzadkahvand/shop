<?php

namespace App\Service\Order\OrderBalanceRefund;

use App\Entity\RefundDocument;
use App\Entity\Transaction;

class OrderBalanceRefundFactory
{
    public function getRefundDocument(): RefundDocument
    {
        return new RefundDocument();
    }

    public function getTransaction(): Transaction
    {
        return new Transaction();
    }
}
