<?php

namespace App\Service\Carrier\COD\Condition;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\OrderShipment;
use App\Service\Carrier\Exceptions\OrderPaymentMethodIsNotOfflineException;

class OrderPaymentMethodIsOfflineCondition implements CODConditionInterface
{
    public function apply(OrderShipment $orderShipment): void
    {
        if ($orderShipment->getOrder()->getPaymentMethod() !== OrderPaymentMethod::OFFLINE) {
            throw new OrderPaymentMethodIsNotOfflineException();
        }
    }
}
