<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\ShippingCategoryName;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemCanNotBePackagedException;
use App\Service\Seller\SellerOrderItem\Exceptions\SellerOrderItemIsRejectedException;
use App\Service\Seller\SellerOrderItem\SellerOrderItemService;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class SellerOrderItemServiceTest extends MockeryTestCase
{
    /**
     * @var SellerOrderItem|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $sellerOrderItemMock;

    /**
     * @var OrderItem|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderItemMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var Seller|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $sellerMock;

    /**
     * @var SellerPackageItem|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $packageItemMock;

    /**
     * @var SellerPackage|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $packageMock;

    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var SellerOrderItemStatusService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $sellerOrderItemStatusService;

    protected SellerOrderItemService $sellerOrderItemService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerOrderItemMock          = Mockery::mock(SellerOrderItem::class);
        $this->orderItemMock                = Mockery::mock(OrderItem::class);
        $this->orderMock                    = Mockery::mock(Order::class);
        $this->sellerMock                   = Mockery::mock(Seller::class);
        $this->packageItemMock              = Mockery::mock(SellerPackageItem::class);
        $this->packageMock                  = Mockery::mock(SellerPackage::class);
        $this->em                           = Mockery::mock(EntityManagerInterface::class);
        $this->sellerOrderItemStatusService = Mockery::mock(SellerOrderItemStatusService::class);

        $this->sellerOrderItemService = new SellerOrderItemService($this->em, $this->sellerOrderItemStatusService);
    }

    protected function tearDown(): void
    {
        unset($this->sellerOrderItemService);

        $this->sellerOrderItemMock          = null;
        $this->orderItemMock                = null;
        $this->orderMock                    = null;
        $this->sellerMock                   = null;
        $this->packageItemMock              = null;
        $this->packageMock                  = null;
        $this->em                           = null;
        $this->sellerOrderItemStatusService = null;
    }

    public function testItThrowExceptionIfOrderStatusIsNotConfirmed(): void
    {
        $this->sellerOrderItemMock->shouldReceive('getOrderItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderItemMock);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::NEW);

        $this->expectException(InvalidSellerOrderStatusException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('The seller order status is invalid!');

        $this->sellerOrderItemService->send($this->sellerOrderItemMock);
    }

    public function testItThrowExceptionIfItemIsRejected(): void
    {
        $this->sellerOrderItemMock->shouldReceive('getOrderItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderItemMock);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->sellerOrderItemMock->shouldReceive('isRejected')->once()->withNoArgs()->andReturnTrue();

        $this->expectException(SellerOrderItemIsRejectedException::class);

        $this->sellerOrderItemService->send($this->sellerOrderItemMock);
    }

    public function testItThrowExceptionIfItemStatusIsNotWaitingForSend(): void
    {
        $this->sellerOrderItemMock->shouldReceive('getOrderItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderItemMock);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->sellerOrderItemMock->shouldReceive('isRejected')->once()->withNoArgs()->andReturnFalse();
        $this->sellerOrderItemMock->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnFalse();

        $this->expectException(SellerOrderItemCanNotBePackagedException::class);

        $this->sellerOrderItemService->send($this->sellerOrderItemMock);
    }

    public function testItReturnsCreatedPackageIfItemIsSentAlready(): void
    {
        $this->sellerOrderItemMock->shouldReceive('getOrderItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderItemMock);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->sellerOrderItemMock->shouldReceive('isRejected')->once()->withNoArgs()->andReturnFalse();
        $this->sellerOrderItemMock->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnTrue();
        $this->sellerOrderItemMock->shouldReceive('isSent')->once()->withNoArgs()->andReturnTrue();

        $this->packageItemMock->shouldReceive('getPackage')
                              ->once()
                              ->withNoArgs()
                              ->andReturn($this->packageMock);

        $this->sellerOrderItemMock->shouldReceive('getPackageItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->packageItemMock);

        $this->sellerOrderItemService->send($this->sellerOrderItemMock);
    }

    public function testItCreateMarkItemAsSent(): void
    {
        $this->sellerOrderItemMock->shouldReceive('getOrderItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderItemMock);

        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getTitle')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(ShippingCategoryName::FMCG);

        $this->orderItemMock->shouldReceive('getOrderShipment')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($orderShipment);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->sellerOrderItemMock->shouldReceive('isRejected')->once()->withNoArgs()->andReturnFalse();
        $this->sellerOrderItemMock->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnTrue();
        $this->sellerOrderItemMock->shouldReceive('isSent')->once()->withNoArgs()->andReturnFalse();

        $this->sellerMock->shouldReceive('addPackage')
                         ->once()
                         ->with(Mockery::type(SellerPackage::class))
                         ->andReturnSelf();

        $this->sellerOrderItemMock->shouldReceive('getSeller')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->sellerMock);

        $this->sellerOrderItemMock->shouldReceive('setPackageItem')
                                  ->once()
                                  ->with(Mockery::type(SellerPackageItem::class))
                                  ->andReturnSelf();

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('commit')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('persist')->once()->with(Mockery::type(SellerPackage::class))->andReturn();
        $this->em->shouldReceive('persist')->once()->with(Mockery::type(SellerPackageItem::class))->andReturn();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturn();

        $this->sellerOrderItemStatusService->shouldReceive('change')
                                           ->once()
                                           ->with($this->sellerOrderItemMock, SellerOrderItemStatus::SENT_BY_SELLER)
                                           ->andReturn();

        $this->sellerOrderItemService->send($this->sellerOrderItemMock);
    }

    public function testItRollbackIfExceptionOccure(): void
    {
        $this->sellerOrderItemMock->shouldReceive('getOrderItem')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->orderItemMock);

        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getTitle')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(ShippingCategoryName::NORMAL);

        $this->orderItemMock->shouldReceive('getOrderShipment')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($orderShipment);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::CONFIRMED);

        $this->sellerOrderItemMock->shouldReceive('isRejected')->once()->withNoArgs()->andReturnFalse();
        $this->sellerOrderItemMock->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnTrue();
        $this->sellerOrderItemMock->shouldReceive('isSent')->once()->withNoArgs()->andReturnFalse();

        $this->sellerMock->shouldReceive('addPackage')
                         ->once()
                         ->with(Mockery::type(SellerPackage::class))
                         ->andReturnSelf();

        $this->sellerOrderItemMock->shouldReceive('getSeller')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn($this->sellerMock);

        $this->sellerOrderItemMock->shouldReceive('setPackageItem')
                                  ->once()
                                  ->with(Mockery::type(SellerPackageItem::class))
                                  ->andReturnSelf();

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('persist')->once()->with(Mockery::type(SellerPackage::class))->andReturn();
        $this->em->shouldReceive('persist')->once()->with(Mockery::type(SellerPackageItem::class))->andReturn();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andThrow(
            Exception::class
        );

        $this->sellerOrderItemStatusService->shouldReceive('change')
                                           ->once()
                                           ->with($this->sellerOrderItemMock, SellerOrderItemStatus::SENT_BY_SELLER)
                                           ->andReturn();

        $this->expectException(Exception::class);

        $this->sellerOrderItemService->send($this->sellerOrderItemMock);
        $this->sellerOrderItemService->send($this->sellerOrderItemMock, 'description of package');
    }
}
