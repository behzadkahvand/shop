<?php

namespace App\Tests\Unit\Service\OrderItemLog;

use App\Entity\Admin;
use App\Entity\OrderItem;
use App\Entity\OrderItemLog;
use App\Service\OrderItemLog\OrderItemLogCreator;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OrderItemLogCreatorTest extends MockeryTestCase
{
    /**
     * @var OrderItemLogCreator
     */
    private $orderItemLogCreator;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|EntityManager|null $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = Mockery::mock(EntityManager::class);

        $this->orderItemLogCreator = new OrderItemLogCreator($this->entityManager);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->orderItemLogCreator);

        $this->entityManager = null;
    }

    public function testCreateWithoutAdmin()
    {
        $orderItem = new OrderItem();

        $this->entityManager->shouldReceive('persist')->once()->with(OrderItemLog::class);
        $orderItemLog = $this->orderItemLogCreator->create($orderItem, 1, 2);

        self::assertEquals($orderItem, $orderItemLog->getOrderItem());
        self::assertEquals(1, $orderItemLog->getQuantityFrom());
        self::assertEquals(2, $orderItemLog->getQuantityTo());
        self::assertNull($orderItemLog->getUser());
    }

    public function testCreateWithAdmin()
    {
        $orderItem = new OrderItem();
        $admin = new Admin();
        $this->entityManager->shouldReceive('persist')->once()->with(OrderItemLog::class);
        $orderItemLog = $this->orderItemLogCreator->create($orderItem, 1, 2, $admin);

        self::assertEquals($orderItem, $orderItemLog->getOrderItem());
        self::assertEquals(1, $orderItemLog->getQuantityFrom());
        self::assertEquals(2, $orderItemLog->getQuantityTo());
        self::assertEquals($admin, $orderItemLog->getUser());
    }
}
