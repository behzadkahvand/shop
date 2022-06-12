<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageType;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\Seller\SellerOrderItem\Exceptions as Ex;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerPackage\SellerPackageFactory;
use App\Service\Seller\SellerPackage\ValidationStrategy\SellerOrderItemValidationStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerPackageFactoryTest
 */
final class SellerPackageFactoryTest extends MockeryTestCase
{
    /*public function testItDontCreatePackageIfOrderIsNotConfirmed(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderStatus::CANCELED);

        $orderItem = \Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getOrderItem')->once()->withNoArgs()->andReturn($orderItem);

        $factory = new SellerPackageFactory(
            \Mockery::mock(EntityManagerInterface::class),
            \Mockery::mock(SellerOrderItemStatusService::class)
        );

        $this->expectException(Ex\InvalidSellerOrderStatusException::class);

        $factory->create([$sellerOrderItem], \Mockery::mock(Seller::class));
    }

    public function testItDontCreatePackageIfSellerOrderItemStatusIsNotWaitingForSend(): void
    {
        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderStatus::CONFIRMED);

        $orderItem = \Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($order);

        $sellerOrderItem = \Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getOrderItem')->once()->withNoArgs()->andReturn($orderItem);
        $sellerOrderItem->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnFalse();

        $factory = new SellerPackageFactory(
            \Mockery::mock(EntityManagerInterface::class),
            \Mockery::mock(SellerOrderItemStatusService::class)
        );

        $this->expectException(Ex\SellerOrderItemCanNotBePackagedException::class);

        $factory->create([$sellerOrderItem], \Mockery::mock(Seller::class));
    }*/

    public function testItDontCreatePackageIfSellerOrderItemsArrayIsEmpty(): void
    {
        $factory = new SellerPackageFactory(
            Mockery::mock(EntityManagerInterface::class),
            Mockery::mock(SellerOrderItemStatusService::class)
        );

        $this->expectException(Ex\AllSellerOrderItemsAlreadySentException::class);

        $factory->create([], SellerPackageType::FMCG, Mockery::mock(Seller::class), $this->getValidationStrategy());
    }

    public function testItRollbackTransactionIfAnyExceptionHappenAfterCreatingPackage(): void
    {
        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('setPackageItem')
                        ->once()
                        ->with(Mockery::type(SellerPackageItem::class))
                        ->andReturnSelf();

        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive('addPackage')->once()->with(
            Mockery::type(SellerPackage::class)
        )->andReturnSelf();

        $em      = Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('persist')->once()->with(
            Mockery::type(SellerPackageItem::class)
        )->andReturn();
        $em->shouldReceive('persist')->once()->with(
            Mockery::type(SellerPackage::class)
        )->andReturn();
        $em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $sellerOrderItemStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $sellerOrderItemStatusService->shouldReceive('change')
                                     ->once()
                                     ->with($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER)
                                     ->andThrow(Exception::class);

        $factory = new SellerPackageFactory($em, $sellerOrderItemStatusService);

        $this->expectException(Exception::class);

        $factory->create([$sellerOrderItem], SellerPackageType::NON_FMCG, $seller, $this->getValidationStrategy());
    }

    public function testItCreatePackage(): void
    {
        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('setPackageItem')
                        ->once()
                        ->with(Mockery::type(SellerPackageItem::class))
                        ->andReturnSelf();

        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive('addPackage')->once()->with(
            Mockery::type(SellerPackage::class)
        )->andReturnSelf();

        $em      = Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('persist')->once()->with(
            Mockery::type(SellerPackageItem::class)
        )->andReturn();
        $em->shouldReceive('persist')->once()->with(
            Mockery::type(SellerPackage::class)
        )->andReturn();
        $em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $em->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $sellerOrderItemStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $sellerOrderItemStatusService->shouldReceive('change')
                                     ->once()
                                     ->with($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER)
                                     ->andReturn();

        $factory = new SellerPackageFactory($em, $sellerOrderItemStatusService);

        $factory->create([$sellerOrderItem], SellerPackageType::FMCG, $seller, $this->getValidationStrategy());
    }

    private function getValidationStrategy(): SellerOrderItemValidationStrategyInterface
    {
        $strategy = Mockery::mock(SellerOrderItemValidationStrategyInterface::class);
        $strategy->shouldReceive('validate')->once()->with(
            Mockery::type('array')
        )->andReturn();

        return $strategy;
    }
}
