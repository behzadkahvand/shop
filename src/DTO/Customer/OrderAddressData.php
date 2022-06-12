<?php

namespace App\DTO\Customer;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\OrderShipment;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class OrderAddressData
 */
final class OrderAddressData
{
    private $customerAddress;

    public function __construct()
    {
    }

    /**
     * @param $customerAddress
     * @return OrderAddressData
     */
    public function setCustomerAddress($customerAddress)
    {
        $this->customerAddress = $customerAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddress()
    {
        return $this->customerAddress;
    }
}
