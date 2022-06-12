<?php

namespace App\Tests\Unit\EventSubscriber\Shipment;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\TransferReason;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\EventSubscriber\Shipment\ShipmentStatusChangeSubscriber;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use App\Tests\Unit\BaseUnitTestCase;
use Generator;
use Mockery;

class ShipmentStatusChangeSubscriberTest extends BaseUnitTestCase
{
    /**
     * @dataProvider shipmentStatusProvider
     */
    public function testShouldDoNothingIfShipmentIsNotCanceled(string $status): void
    {
        $walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);

        $event = Mockery::mock(OrderShipmentStatusChanged::class);
        $shipment = new OrderShipment();
        $shipment->setStatus($status);
        $event->shouldReceive('getOrderShipment')->once()->andReturn($shipment);

        $sut = new ShipmentStatusChangeSubscriber($walletPaymentHandler);

        $sut->onShipmentCancel($event);
    }

    public function shipmentStatusProvider(): Generator
    {
        $statuses = array_values(OrderShipmentStatus::toArray());
        unset($statuses[array_search(OrderShipmentStatus::CANCELED, $statuses)]);

        foreach ($statuses as $status) {
            yield [$status];
        }
    }

    public function testShouldHandleWalletPaymentsIfShipmentIsCanceled(): void
    {
        $walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);

        $event = Mockery::mock(OrderShipmentStatusChanged::class);
        $shipment = new OrderShipment();
        $order = new Order();
        $shipment->setOrder($order);
        $shipment->setStatus(OrderShipmentStatus::CANCELED);
        $event->shouldReceive('getOrderShipment')->once()->andReturn($shipment);
        $walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($order, TransferReason::ORDER_REFUND)
            ->andReturnNull();

        $sut = new ShipmentStatusChangeSubscriber($walletPaymentHandler);

        $sut->onShipmentCancel($event);
    }
}
