<?php

namespace App\Service\CustomerLegalAccount;

use App\Entity\CustomerLegalAccount;

class CustomerLegalAccountFactory
{
    public function getCustomerLegalAccount(): CustomerLegalAccount
    {
        return new CustomerLegalAccount();
    }
}
