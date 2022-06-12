<?php

namespace App\Tests\Unit\Service\OrderShipment\PartialOrderShipmentTransaction;

use App\Entity\Transaction;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\PartialOrderShipmentTransactionFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PartialOrderShipmentTransactionFactoryTest extends MockeryTestCase
{
    public function testItCanGetTransaction()
    {
        $factory = new PartialOrderShipmentTransactionFactory();

        $result = $factory->createTransaction();

        self::assertInstanceOf(Transaction::class, $result);
    }
}
