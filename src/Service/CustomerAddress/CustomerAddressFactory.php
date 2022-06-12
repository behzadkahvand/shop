<?php

namespace App\Service\CustomerAddress;

use App\Entity\CustomerAddress;

class CustomerAddressFactory
{
    public function getCustomerAddress(): CustomerAddress
    {
        return new CustomerAddress();
    }
}
