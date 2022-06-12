<?php

namespace App\Tests\Unit\Service\OrderLegalAccount;

use App\Entity\OrderLegalAccount;
use App\Service\OrderLegalAccount\OrderLegalAccountFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderLegalAccountFactoryTest extends MockeryTestCase
{
    public function testItCanGetOrderLegalAccount()
    {
        $factory = new OrderLegalAccountFactory();

        $result = $factory->getOrderLegalAccount();

        self::assertInstanceOf(OrderLegalAccount::class, $result);
    }
}
