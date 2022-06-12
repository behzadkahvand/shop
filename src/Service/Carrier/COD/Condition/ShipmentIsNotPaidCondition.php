<?php

namespace App\Service\Carrier\COD\Condition;

use App\Dictionary\TransactionStatus;
use App\Entity\OrderShipment;
use App\Service\Carrier\Exceptions\ShipmentAlreadyHasBeenPaidException;

class ShipmentIsNotPaidCondition implements CODConditionInterface
{
    public function apply(OrderShipment $orderShipment): void
    {
        $transaction = $orderShipment->getTransaction();

        if (($transaction !== null) && $transaction->getStatus() === TransactionStatus::SUCCESS) {
            throw new ShipmentAlreadyHasBeenPaidException();
        }
    }
}
