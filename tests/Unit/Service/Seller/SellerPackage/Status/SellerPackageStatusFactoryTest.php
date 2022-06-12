<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Service\Seller\SellerPackage\Status\AbstractSellerPackageStatus;
use App\Service\Seller\SellerPackage\Status\Exceptions\CouldNotFindSellerPackageStatusException;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusFactory;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerPackageStatusFactoryTest extends MockeryTestCase
{
    //@todo must be refactor tests!
    public function testItCreateSellerPackageStatusObject()
    {
        $status = new class () extends AbstractSellerPackageStatus {
            public function getName(): string
            {
                return SellerPackageStatus::RECEIVED;
            }

            protected function getCondition(): Closure
            {
                return fn() => true;
            }

            protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool
            {
                return true;
            }
        };

        $factory = new SellerPackageStatusFactory([$status]);

        $sellerPackage   = Mockery::mock(SellerPackage::class);
        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);

        $sellerPackage->expects('getPackageOrderItems')
                      ->withNoArgs()
                      ->andReturn(new ArrayCollection([$sellerOrderItem, $sellerOrderItem]));

        $sellerOrderItem->expects('getStatus')
                        ->twice()
                        ->withNoArgs()
                        ->andReturn(SellerOrderItemStatus::RECEIVED);

        self::assertSame($status, $factory->create($sellerPackage));
    }

    public function testItThrowExceptionWhenStatusNotFound()
    {
        $status = new class () extends AbstractSellerPackageStatus {
            public function getName(): string
            {
                return SellerPackageStatus::RECEIVED;
            }

            protected function getCondition(): Closure
            {
                return fn() => true;
            }

            protected function check($sellerOrderItemItemCount, $filteredSellerOrderSellerOrderItemCount): bool
            {
                return false;
            }
        };

        $factory = new SellerPackageStatusFactory([$status]);

        self::expectException(CouldNotFindSellerPackageStatusException::class);

        $factory->create(new SellerPackage());
    }
}
