<?php

namespace App\Service\OrderShipment\PartialOrderShipmentTransaction;

use App\Entity\Transaction;

class PartialOrderShipmentTransactionFactory
{
    public function createTransaction(): Transaction
    {
        return new Transaction();
    }
}
