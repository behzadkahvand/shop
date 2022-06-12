<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\AbstractSellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\Exceptions\InvalidSellerOrderItemStatusTransitionException;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AbstractSellerOrderItemStatusTest extends MockeryTestCase
{
    protected $sellerOrderItemMock;

    protected $orderShipmentStatusServiceMock;

    private $orderShipmentMock;

    protected $sellerOrderItemStatusServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerOrderItemMock            = \Mockery::mock(SellerOrderItem::class);
        $this->orderShipmentStatusServiceMock = \Mockery::mock(OrderShipmentStatusService::class);
        $this->orderShipmentMock              = \Mockery::mock(OrderShipment::class);

        $args = [$this->orderShipmentStatusServiceMock];

        $this->sellerOrderItemStatusServiceMock = new class (...$args) extends AbstractSellerOrderItemStatus {
            protected function getName(): string
            {
                return 'test';
            }
        };
    }

    protected function tearDown(): void
    {
        unset(
            $this->sellerOrderItemMock,
            $this->orderShipmentStatusServiceMock,
            $this->orderShipmentMock,
            $this->sellerOrderItemStatusServiceMock
        );
    }

    /**
     * @dataProvider provider
     *
     * @param string $method
     */
    public function testSettingStatus(string $method): void
    {
        $expectedStatus = constant(SellerOrderItemStatus::class . '::' . strtoupper(snake_case($method)));

        $this->sellerOrderItemMock->shouldReceive('setStatus')
                                  ->once()
                                  ->with($expectedStatus)
                                  ->andReturnSelf();

        $this->sellerOrderItemStatusServiceMock->$method($this->sellerOrderItemMock);
    }

    public function testStoraged()
    {
        $this->sellerOrderItemMock->shouldReceive('getStatus')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn(SellerOrderItemStatus::WAITING);

        $this->expectException(InvalidSellerOrderItemStatusTransitionException::class);
        $this->expectExceptionMessage("Seller order item status transition from WAITING to STORAGED is invalid!");

        $this->sellerOrderItemStatusServiceMock->storaged($this->sellerOrderItemMock);
    }

    public function provider(): iterable
    {
        $methods = get_class_methods(AbstractSellerOrderItemStatus::class);

        unset(
            $methods[array_search('__construct', $methods, true)],
            $methods[array_search('canceledByUser', $methods, true)],
            $methods[array_search('support', $methods, true)],
            $methods[array_search('storaged', $methods, true)],
        );

        return array_map(static fn($method) => [$method], $methods);
    }

    public function testItSetSellerOrderItemStatusToCancelByUserAndDontCancelAlreadyCanceledOrderShipment(): void
    {
        $this->orderShipmentMock->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnTrue();

        $this->sellerOrderItemMock->shouldReceive('setStatus')
                                  ->once()
                                  ->with(SellerOrderItemStatus::CANCELED_BY_USER)
                                  ->andReturnSelf();
        $this->sellerOrderItemMock->shouldReceive('getOrderItem->getOrderShipment')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderShipmentMock);

        $this->orderShipmentStatusServiceMock->shouldNotReceive('change');

        $this->sellerOrderItemStatusServiceMock->canceledByUser($this->sellerOrderItemMock);
    }

    public function testItSetSellerOrderItemStatusToCancelByUserAnDontCancelOrderShipment(): void
    {
        $this->orderShipmentMock->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnFalse();
        $this->orderShipmentMock->shouldReceive('getOrderItemsCount')->once()->withNoArgs()->andReturn(2);
        $this->orderShipmentMock->shouldReceive('getOrderItems')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(new ArrayCollection([]));

        $this->sellerOrderItemMock->shouldReceive('setStatus')
                                  ->once()
                                  ->with(SellerOrderItemStatus::CANCELED_BY_USER)
                                  ->andReturnSelf();
        $this->sellerOrderItemMock->shouldReceive('getOrderItem->getOrderShipment')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderShipmentMock);

        $this->orderShipmentStatusServiceMock->shouldNotReceive('change');

        $this->sellerOrderItemStatusServiceMock->canceledByUser($this->sellerOrderItemMock);
    }

    public function testItSetSellerOrderItemStatusToCancelByUserAnCancelOrderShipment(): void
    {
        $this->orderShipmentMock->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnFalse();
        $this->orderShipmentMock->shouldReceive('getOrderItemsCount')->once()->withNoArgs()->andReturn(1);

        $this->sellerOrderItemMock->shouldReceive('setStatus')
                                  ->once()
                                  ->with(SellerOrderItemStatus::CANCELED_BY_USER)
                                  ->andReturnSelf();
        $this->sellerOrderItemMock->shouldReceive('getOrderItem->getOrderShipment')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderShipmentMock);

        $this->orderShipmentStatusServiceMock->shouldReceive('change')
                                             ->once()
                                             ->with($this->orderShipmentMock, OrderShipmentStatus::CANCELED)
                                             ->andReturn();

        $this->sellerOrderItemStatusServiceMock->canceledByUser($this->sellerOrderItemMock);
    }

    public function testItSetSellerOrderItemStatusToCancelByUserAnDontChangeOrderShipmentStatusToWarehouse(): void
    {
        $this->orderShipmentMock->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnFalse();
        $this->orderShipmentMock->shouldReceive('getOrderItemsCount')->once()->withNoArgs()->andReturn(2);
        $this->orderShipmentMock->shouldReceive('getOrderItems')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(new ArrayCollection([]));

        $this->sellerOrderItemMock->shouldReceive('setStatus')
                                  ->once()
                                  ->with(SellerOrderItemStatus::CANCELED_BY_USER)
                                  ->andReturnSelf();
        $this->sellerOrderItemMock->shouldNotReceive('getOrderItem->getOrderShipment')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderShipmentMock);

        $this->orderShipmentStatusServiceMock->shouldNotReceive('change');

        $this->sellerOrderItemStatusServiceMock->canceledByUser($this->sellerOrderItemMock);
    }

    public function testItSetSellerOrderItemStatusToCancelByUserAnChangeOrderShipmentStatusToWarehouse(): void
    {
        $this->orderShipmentMock->shouldReceive('isCanceled')->once()->withNoArgs()->andReturnFalse();
        $this->orderShipmentMock->shouldReceive('getOrderItemsCount')->once()->withNoArgs()->andReturn(2);
        $this->orderShipmentMock->shouldReceive('getOrderItems')
                                ->once()
                                ->withNoArgs()
                                ->andReturnUsing(function () {
                                    $soi = \Mockery::mock(SellerOrderItem::class);
                                    $soi->shouldReceive(['isRejected' => false, 'isStoraged' => true])
                                        ->once()
                                        ->withNoArgs();

                                    $orderItem = \Mockery::mock(OrderItem::class);
                                    $orderItem->shouldReceive('getSellerOrderItem')
                                              ->twice()
                                              ->withNoArgs()
                                              ->andReturn($soi);

                                    return new ArrayCollection([$orderItem]);
                                });

        $this->sellerOrderItemMock->shouldReceive('setStatus')
                                  ->once()
                                  ->with(SellerOrderItemStatus::CANCELED_BY_USER)
                                  ->andReturnSelf();
        $this->sellerOrderItemMock->shouldNotReceive('getOrderItem->getOrderShipment')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderShipmentMock);

        $this->orderShipmentStatusServiceMock->shouldReceive('change')
                                             ->once()
                                             ->with($this->orderShipmentMock, OrderShipmentStatus::WAREHOUSE);

        $this->sellerOrderItemStatusServiceMock->canceledByUser($this->sellerOrderItemMock);
    }
}
