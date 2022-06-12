<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Service\Seller\SellerPackage\Status\ReceivedSellerPackageStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class ReceivedSellerPackageStatusTest extends MockeryTestCase
{
    public function testGetName()
    {
        $status = new ReceivedSellerPackageStatus();

        self::assertSame($status->getName(), SellerPackageStatus::RECEIVED);
    }

    public function testSupportTrue()
    {
        $sellerOrderItem1 = new SellerOrderItem();
        $sellerOrderItem1->setStatus(SellerOrderItemStatus::RECEIVED);

        $sellerOrderItem2 = new SellerOrderItem();
        $sellerOrderItem2->setStatus(SellerOrderItemStatus::RECEIVED);

        $sellerOrderItem3 = new SellerOrderItem();
        $sellerOrderItem3->setStatus(SellerOrderItemStatus::CANCELED_BY_SELLER);

        $sellerPackageMock = m::mock(SellerPackage::class);
        $sellerPackageMock->shouldReceive('getPackageOrderItems')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([$sellerOrderItem1, $sellerOrderItem2, $sellerOrderItem3]));

        $status = new ReceivedSellerPackageStatus();

        self::assertTrue($status->support($sellerPackageMock));
    }

    public function testSupportFalse()
    {
        $sellerOrderItem1 = new SellerOrderItem();
        $sellerOrderItem1->setStatus(SellerOrderItemStatus::SENT_BY_SELLER);

        $sellerOrderItem2 = new SellerOrderItem();
        $sellerOrderItem2->setStatus(SellerOrderItemStatus::RECEIVED);

        $sellerPackageMock = m::mock(SellerPackage::class);
        $sellerPackageMock->shouldReceive('getPackageOrderItems')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([$sellerOrderItem1, $sellerOrderItem2]));

        $status = new ReceivedSellerPackageStatus();

        self::assertFalse($status->support($sellerPackageMock));
    }

    public function testSupportFalseWhenSellerOrderItemsIsCanceled(): void
    {
        $sellerOrderItem1 = new SellerOrderItem();
        $sellerOrderItem1->setStatus(SellerOrderItemStatus::CANCELED_BY_SELLER);

        $sellerOrderItem2 = new SellerOrderItem();
        $sellerOrderItem2->setStatus(SellerOrderItemStatus::CANCELED_BY_USER);

        $sellerPackageMock = m::mock(SellerPackage::class);
        $sellerPackageMock->shouldReceive('getPackageOrderItems')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(new ArrayCollection([$sellerOrderItem1, $sellerOrderItem2]));

        $status = new ReceivedSellerPackageStatus();

        self::assertFalse($status->support($sellerPackageMock));
    }
}
