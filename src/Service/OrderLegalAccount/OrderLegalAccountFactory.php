<?php

namespace App\Service\OrderLegalAccount;

use App\Entity\OrderLegalAccount;

class OrderLegalAccountFactory
{
    public function getOrderLegalAccount(): OrderLegalAccount
    {
        return new OrderLegalAccount();
    }
}
