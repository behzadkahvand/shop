<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItemStatusLog;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Admin;
use App\Entity\AdminUserSellerOrderItemStatusLog;
use App\Entity\SellerOrderItem;
use App\Service\Seller\SellerOrderItemStatusLog\CreateSellerOrderItemStatusLogService;
use App\Service\Seller\SellerOrderItemStatusLog\SellerOrderItemStatusLogFactory;
use App\Service\Seller\SellerOrderItemStatusLog\ValueObjects\CreateSellerOrderItemStatusLogValueObject;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Symfony\Component\Security\Core\Security;

class CreateSellerOrderItemStatusLogServiceTest extends MockeryTestCase
{
    public function testPerform()
    {
        $manager         = m::mock(EntityManagerInterface::class);
        $factory         = m::mock(SellerOrderItemStatusLogFactory::class);
        $security        = m::mock(Security::class);
        $sellerOrderItem = new SellerOrderItem();
        $valueObject     = new CreateSellerOrderItemStatusLogValueObject();
        $valueObject->setSellerOrderItem($sellerOrderItem)
                    ->setStatusTo(SellerOrderItemStatus::CANCELED_BY_SELLER)
                    ->setStatusFrom(SellerOrderItemStatus::DAMAGED);

        $user     = new Admin();
        $adminLog = m::mock(AdminUserSellerOrderItemStatusLog::class);
        $adminLog->shouldReceive('setSellerOrderItem')
                 ->once()
                 ->with($sellerOrderItem)
                 ->andReturn($adminLog);
        $adminLog->shouldReceive('setStatusFrom')->once()->with(SellerOrderItemStatus::DAMAGED)->andReturn($adminLog);
        $adminLog->shouldReceive('setStatusTo')
                 ->once()
                 ->with(SellerOrderItemStatus::CANCELED_BY_SELLER)
                 ->andReturn($adminLog);

        $security->shouldReceive('getUser')->once()->withNoArgs()->andReturn($user);
        $factory->shouldReceive('getSellerOrderItemStatusLog')
                ->once()
                ->with($user)
                ->andReturn($adminLog);

        $manager->shouldReceive('persist')
                ->once()
                ->with(m::type(AdminUserSellerOrderItemStatusLog::class))
                ->andReturn();
        $manager->shouldReceive('flush')
                ->once()
                ->withNoArgs()
                ->andReturn();

        (new CreateSellerOrderItemStatusLogService($manager, $factory, $security))->perform($valueObject);
    }
}
