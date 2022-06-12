<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage\EventSubscriber;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageItem;
use App\Service\Seller\SellerPackage\Events\SellerOrderItemStatusChangeEvent;
use App\Service\Seller\SellerPackage\EventSubscriber\SellerPackageSubscriber;
use App\Service\Seller\SellerPackage\Status\AbstractSellerPackageStatus;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusFactory;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerPackageSubscriberTest extends MockeryTestCase
{
    public function testOnSellerOrderItemStatusChange(): void
    {
        $status = new class () extends AbstractSellerPackageStatus {
            public function support(SellerPackage $sellerPackage): bool
            {
                return true;
            }

            public function getName(): string
            {
                return SellerPackageStatus::RECEIVED;
            }

            protected function getCondition(): \Closure
            {
                return fn() => true;
            }

            protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool
            {
                return true;
            }
        };
        $sellerPackage = m::mock(SellerPackage::class);
        $sellerOrderItem = m::mock(SellerOrderItem::class);
        $sellerPackageItem = m::mock(SellerPackageItem::class);

        $sellerOrderItem->shouldReceive('getPackageItem')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($sellerPackageItem);

        $sellerPackageItem->shouldReceive('getPackage')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($sellerPackage);

        $sellerPackageStatusFactory = new SellerPackageStatusFactory([$status]);

        $sellerPackageStatusServiceMock = m::mock(SellerPackageStatusService::class);
        $sellerPackageStatusServiceMock->shouldReceive('change')
                                       ->once()
                                       ->withArgs([$sellerPackage, $status->getName(), null])
                                       ->andReturn();

        $listener = new SellerPackageSubscriber($sellerPackageStatusServiceMock, $sellerPackageStatusFactory);
        $listener->onSellerOrderItemStatusChange(new SellerOrderItemStatusChangeEvent($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER, SellerOrderItemStatus::RECEIVED));
    }

    public function testOnSellerOrderItemStatusChangeFactoryThrowException(): void
    {
        $status = new class () extends AbstractSellerPackageStatus {
            public function support(SellerPackage $sellerPackage): bool
            {
                return false;
            }

            public function getName(): string
            {
                return SellerPackageStatus::RECEIVED;
            }

            protected function getCondition(): \Closure
            {
                return fn() => true;
            }

            protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool
            {
                return true;
            }
        };
        $sellerPackage = m::mock(SellerPackage::class);
        $sellerOrderItem = m::mock(SellerOrderItem::class);
        $sellerPackageItem = m::mock(SellerPackageItem::class);

        $sellerOrderItem->shouldReceive('getPackageItem')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($sellerPackageItem);

        $sellerPackageItem->shouldReceive('getPackage')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($sellerPackage);

        $sellerPackageStatusFactory = new SellerPackageStatusFactory([$status]);

        $sellerPackageStatusServiceMock = m::mock(SellerPackageStatusService::class);

        $listener = new SellerPackageSubscriber($sellerPackageStatusServiceMock, $sellerPackageStatusFactory);
        $listener->onSellerOrderItemStatusChange(new SellerOrderItemStatusChangeEvent($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER, SellerOrderItemStatus::RECEIVED));
    }

    public function testOnSellerOrderItemStatusChangePackageNotFound(): void
    {
        $status = new class () extends AbstractSellerPackageStatus {
            public function support(SellerPackage $sellerPackage): bool
            {
                return false;
            }

            public function getName(): string
            {
                return SellerPackageStatus::RECEIVED;
            }

            protected function getCondition(): \Closure
            {
                return fn() => true;
            }

            protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool
            {
                return true;
            }
        };
        $sellerOrderItem = m::mock(SellerOrderItem::class);
        $sellerPackageItem = null;

        $sellerOrderItem->shouldReceive('getPackageItem')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($sellerPackageItem);

        $sellerPackageStatusFactory = new SellerPackageStatusFactory([$status]);

        $sellerPackageStatusServiceMock = m::mock(SellerPackageStatusService::class);

        $listener = new SellerPackageSubscriber($sellerPackageStatusServiceMock, $sellerPackageStatusFactory);
        $listener->onSellerOrderItemStatusChange(new SellerOrderItemStatusChangeEvent($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER, SellerOrderItemStatus::RECEIVED));
    }
}
