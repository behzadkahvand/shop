<?php

namespace App\Service\Carrier\Inquiry;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\OrderShipment;
use App\Service\Carrier\Exceptions\DuplicatePaymentException;
use App\Service\Carrier\Exceptions\InvalidPaymentMethodException;

class InquiryService
{
    public function validateOrderShipment(OrderShipment $orderShipment): void
    {
        if ($orderShipment->getOrder()->getPaymentMethod() !== OrderPaymentMethod::OFFLINE) {
            throw new InvalidPaymentMethodException();
        }

        if ($orderShipment->getTransaction() !== null) {
            throw new DuplicatePaymentException();
        }
    }
}
