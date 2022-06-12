<?php

namespace App\Tests\Unit\Service\OrderStatusLog;

use App\Entity\OrderStatusLog;
use App\Service\OrderStatusLog\OrderStatusLogFactory;
use PHPUnit\Framework\TestCase;

class OrderStatusLogFactoryTest extends TestCase
{
    public function testItCanGetOrderStatusLog()
    {
        $orderStatusLogFactory = new OrderStatusLogFactory();

        $result = $orderStatusLogFactory->getOrderStatusLog();

        self::assertInstanceOf(OrderStatusLog::class, $result);
    }
}
