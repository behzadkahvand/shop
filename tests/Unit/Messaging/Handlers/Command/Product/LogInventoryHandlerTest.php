<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Messaging\Handlers\Command\Product\LogInventoryHandler;
use App\Messaging\Messages\Command\Product\LogInventory;
use App\Service\Log\DataLoggerService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class LogInventoryHandlerTest extends BaseUnitTestCase
{
    public function testItCanHandleLogInventoryMessage(): void
    {
        $dataLoggerMock   = Mockery::mock(DataLoggerService::class);
        $logInventoryMock = Mockery::mock(LogInventory::class);

        $sut = new LogInventoryHandler($dataLoggerMock);

        $logInventoryMock->expects('getLoggableProperties')
                         ->withNoArgs()
                         ->andReturns(['properties']);

        $dataLoggerMock->expects('logInventory')
                       ->with(['properties'])
                       ->andReturns();

        ($sut)($logInventoryMock);
    }
}
