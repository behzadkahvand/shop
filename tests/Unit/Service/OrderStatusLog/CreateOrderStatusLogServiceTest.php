<?php

namespace App\Tests\Unit\Service\OrderStatusLog;

use App\Dictionary\OrderStatus;
use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderStatusLog;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\OrderStatusLogFactory;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CreateOrderStatusLogServiceTest extends MockeryTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $em;

    /**
     * @var \App\Service\OrderStatusLog\OrderStatusLogFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $orderStatusLogFactoryMock;

    /**
     * @var \App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $createOrderStatusLogValueObjMock;

    /**
     * @var \App\Entity\Order|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var \App\Entity\Admin|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $adminMock;

    /**
     * @var \App\Entity\OrderStatusLog|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $orderStatusLogMock;

    /**
     * @var \App\Service\OrderStatusLog\CreateOrderStatusLogService
     */
    protected CreateOrderStatusLogService $createOrderStatusLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManager::class);

        $this->orderStatusLogFactoryMock = Mockery::mock(OrderStatusLogFactory::class);

        $this->createOrderStatusLogValueObjMock = Mockery::mock(CreateOrderStatusLogValueObject::class);

        $this->orderMock = Mockery::mock(Order::class);

        $this->adminMock = Mockery::mock(Admin::class);

        $this->orderStatusLogMock = Mockery::mock(OrderStatusLog::class);

        $this->createOrderStatusLog = new CreateOrderStatusLogService(
            $this->em,
            $this->orderStatusLogFactoryMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->createOrderStatusLog);

        $this->em = null;
        $this->orderStatusLogFactoryMock = null;
        $this->createOrderStatusLogValueObjMock = null;
        $this->orderMock = null;
        $this->adminMock = null;
        $this->orderStatusLogMock = null;
    }

    public function testItCanCreateOrderStatusLog()
    {
        $this->orderStatusLogFactoryMock->shouldReceive('getOrderStatusLog')->once()->
            andReturn($this->orderStatusLogMock);

        $this->createOrderStatusLogValueObjMock->shouldReceive('getOrder')->once()->
            andReturn($this->orderMock);
        $this->createOrderStatusLogValueObjMock->shouldReceive('getStatusFrom')->once()->
            andReturn(OrderStatus::WAIT_CUSTOMER);
        $this->createOrderStatusLogValueObjMock->shouldReceive('getStatusTo')->once()->
            andReturn(OrderStatus::CALL_FAILED);
        $this->createOrderStatusLogValueObjMock->shouldReceive('getUser')->once()->
            andReturn($this->adminMock);

        $this->orderStatusLogMock->shouldReceive('setOrder')->once()->andReturn($this->orderStatusLogMock);
        $this->orderStatusLogMock->shouldReceive('setStatusFrom')->once()->andReturn($this->orderStatusLogMock);
        $this->orderStatusLogMock->shouldReceive('setStatusTo')->once()->andReturn($this->orderStatusLogMock);
        $this->orderStatusLogMock->shouldReceive('setUser')->once()->andReturn($this->orderStatusLogMock);

        $this->em->shouldReceive('persist')->once();
        $this->em->shouldReceive('flush')->once();

        $this->createOrderStatusLog->perform($this->createOrderStatusLogValueObjMock);
    }
}
