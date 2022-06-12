<?php

namespace App\Tests\Unit\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerPackageStatus;
use App\Entity\Admin;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageStatusLog;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerPackage\Status\Exceptions\InvalidSellerPackageStatusException;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerPackageStatusServiceTest
 */
final class SellerPackageStatusServiceTest extends MockeryTestCase
{
    public function testItThrowExceptionIfStatusIsNotValid(): void
    {
        $status = 'INVALID_STATUS';

        $this->expectException(InvalidSellerPackageStatusException::class);
        $this->expectExceptionMessage("{$status} is not a valid seller package status.");

        $service = new SellerPackageStatusService(
            \Mockery::mock(EntityManagerInterface::class),
            \Mockery::mock(SellerOrderItemStatusService::class)
        );

        $service->change(\Mockery::mock(SellerPackage::class), $status, null);
    }

    public function testItChangeSellerPackageStatusOnly(): void
    {
        $status    = SellerPackageStatus::PARTIAL_RECEIVED;
        $admin     = \Mockery::mock(Admin::class);
        $log       = \Mockery::type(SellerPackageStatusLog::class);

        $package = \Mockery::mock(SellerPackage::class);
        $package->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(SellerPackageStatus::SENT);
        $package->shouldReceive('setStatus')->once()->with($status)->andReturnSelf();
        $package->shouldReceive('addStatusLog')
                ->once()
                ->with($log)
                ->andReturnSelf();

        $em = \Mockery::mock(EntityManagerInterface::class);
        $em->shouldReceive('persist')->once()->with($log)->andReturn();
        $em->shouldReceive('flush')->once()->withNoArgs()->andReturn();

        $orderItemStatusService = \Mockery::mock(SellerOrderItemStatusService::class);

        $service = new SellerPackageStatusService($em, $orderItemStatusService);

        $service->change($package, $status, $admin);
    }
}
