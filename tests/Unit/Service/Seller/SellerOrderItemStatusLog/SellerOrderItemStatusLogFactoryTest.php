<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItemStatusLog;

use App\Entity\Admin;
use App\Entity\AdminUserSellerOrderItemStatusLog;
use App\Entity\Customer;
use App\Entity\CustomerUserSellerOrderItemStatusLog;
use App\Entity\Seller;
use App\Entity\SellerUserSellerOrderItemStatusLog;
use App\Service\Seller\SellerOrderItemStatusLog\SellerOrderItemStatusLogFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerOrderItemStatusLogFactoryTest extends MockeryTestCase
{
    public function testFactoryReturnsAdmin()
    {
        $user   = new Admin();
        $result = (new SellerOrderItemStatusLogFactory())->getSellerOrderItemStatusLog($user);

        self::assertInstanceOf(AdminUserSellerOrderItemStatusLog::class, $result);
    }

    public function testFactoryReturnsSeller()
    {
        $user   = new Seller();
        $result = (new SellerOrderItemStatusLogFactory())->getSellerOrderItemStatusLog($user);

        self::assertInstanceOf(SellerUserSellerOrderItemStatusLog::class, $result);
    }

    public function testFactoryReturnsCustomer()
    {
        $result = (new SellerOrderItemStatusLogFactory())->getSellerOrderItemStatusLog(null);

        self::assertInstanceOf(CustomerUserSellerOrderItemStatusLog::class, $result);
    }

    public function testFactoryThrowException()
    {
        $user = new Customer();
        $result = (new SellerOrderItemStatusLogFactory())->getSellerOrderItemStatusLog($user);

        self::assertInstanceOf(CustomerUserSellerOrderItemStatusLog::class, $result);
    }
}
