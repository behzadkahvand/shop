<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Service\Seller\SellerPackage\Status\PartialReceivedSellerPackageStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class PartialReceivedSellerPackageStatusTest extends MockeryTestCase
{
    public function testGetName()
    {
        $status = new PartialReceivedSellerPackageStatus();

        self::assertSame($status->getName(), SellerPackageStatus::PARTIAL_RECEIVED);
    }

    public function testSupportTrue()
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

        $status = new PartialReceivedSellerPackageStatus();

        self::assertTrue($status->support($sellerPackageMock));
    }

    public function testSupportFalse()
    {
        $sellerOrderItem1 = new SellerOrderItem();
        $sellerOrderItem1->setStatus(SellerOrderItemStatus::SENT_BY_SELLER);

        $sellerOrderItem2 = new SellerOrderItem();
        $sellerOrderItem2->setStatus(SellerOrderItemStatus::SENT_BY_SELLER);

        $sellerPackageMock = m::mock(SellerPackage::class);
        $sellerPackageMock->shouldReceive('getPackageOrderItems')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(new ArrayCollection([$sellerOrderItem1, $sellerOrderItem2]));

        $status = new PartialReceivedSellerPackageStatus();

        self::assertFalse($status->support($sellerPackageMock));
    }
}
