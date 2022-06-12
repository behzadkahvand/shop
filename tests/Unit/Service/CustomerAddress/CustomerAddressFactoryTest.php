<?php

namespace App\Tests\Unit\Service\CustomerAddress;

use App\Entity\CustomerAddress;
use App\Service\CustomerAddress\CustomerAddressFactory;
use PHPUnit\Framework\TestCase;

class CustomerAddressFactoryTest extends TestCase
{
    public function testItCanGetCustomerAddress()
    {
        $factory = new CustomerAddressFactory();

        $result = $factory->getCustomerAddress();

        self::assertInstanceOf(CustomerAddress::class, $result);
    }
}
