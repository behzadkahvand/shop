<?php

namespace App\Tests\Unit\Service\Carrier\COD\Condition;

use App\Dictionary\TransactionStatus;
use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Service\Carrier\COD\Condition\ShipmentIsNotPaidCondition;
use App\Service\Carrier\Exceptions\ShipmentAlreadyHasBeenPaidException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class ShipmentIsNotPaidConditionTest extends MockeryTestCase
{
    public function testItThrowAnExceptionWhenShipmentAlreadyHasBeenPaid(): void
    {
        $this->expectException(ShipmentAlreadyHasBeenPaidException::class);

        $orderShipment = m::mock(OrderShipment::class);
        $transaction = m::mock(Transaction::class);

        $orderShipment->shouldReceive('getTransaction')
            ->once()
            ->withNoArgs()
            ->andReturn($transaction);

        $transaction->shouldReceive('getStatus')
            ->once()
            ->withNoArgs()
            ->andReturn(TransactionStatus::SUCCESS);

        (new ShipmentIsNotPaidCondition())->apply($orderShipment);
    }
}
