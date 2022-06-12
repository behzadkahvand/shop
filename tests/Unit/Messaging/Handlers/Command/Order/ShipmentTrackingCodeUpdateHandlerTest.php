<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Order;

use App\Messaging\Handlers\Command\Order\ShipmentTrackingCodeUpdateHandler;
use App\Messaging\Messages\Command\Order\ShipmentTrackingCodeUpdate;
use App\Service\OrderShipment\ShipmentTrackingCodeUpdateService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class ShipmentTrackingCodeUpdateHandlerTest extends BaseUnitTestCase
{
    public function testItCanUpdateShipmentTrackingCode(): void
    {
        $shipmentTrackingCodeUpdateMock = Mockery::mock(ShipmentTrackingCodeUpdateService::class);
        $messageMock                    = Mockery::mock(ShipmentTrackingCodeUpdate::class);

        $sut = new ShipmentTrackingCodeUpdateHandler($shipmentTrackingCodeUpdateMock);

        $messageMock->expects('getTrackingCodeID')->withNoArgs()->andReturns(12);

        $shipmentTrackingCodeUpdateMock->expects('processBatchUpdateTrackingCodes')
                                       ->with(12)
                                       ->andReturns();

        ($sut)($messageMock);
    }
}
