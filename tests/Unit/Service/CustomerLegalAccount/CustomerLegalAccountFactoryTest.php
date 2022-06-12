<?php

namespace App\Tests\Unit\Service\CustomerLegalAccount;

use App\Entity\CustomerLegalAccount;
use App\Service\CustomerLegalAccount\CustomerLegalAccountFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CustomerLegalAccountFactoryTest extends MockeryTestCase
{
    public function testItCanGetCustomerLegalAccount()
    {
        $factory = new CustomerLegalAccountFactory();

        $result = $factory->getCustomerLegalAccount();

        self::assertInstanceOf(CustomerLegalAccount::class, $result);
    }
}
