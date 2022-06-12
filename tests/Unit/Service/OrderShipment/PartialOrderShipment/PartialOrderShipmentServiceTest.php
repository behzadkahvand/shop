<?php

namespace App\Tests\Unit\Service\OrderShipment\PartialOrderShipment;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\PromotionDiscount;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingPeriod;
use App\Entity\Transaction;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderShipment\PartialOrderShipment\PartialOrderShipmentService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Security\Core\Security;

class PartialOrderShipmentServiceTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    private ?PartialOrderShipmentService $partialOrderShipmentService;

    private ?OrderShipmentStatusService $orderShipmentStatusServiceMock;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|Security|null $security;


    protected function setUp(): void
    {
        parent::setUp();

        $this->em                              = Mockery::mock(EntityManagerInterface::class);
        $this->orderShipmentStatusServiceMock  = Mockery::mock(OrderShipmentStatusService::class);
        $this->security  = Mockery::mock(Security::class);
        $this->partialOrderShipmentService     = new PartialOrderShipmentService(
            $this->em,
            $this->orderShipmentStatusServiceMock,
            $this->security
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em                              = null;
        unset($this->partialOrderShipmentService);
    }

    public function testItCanCloneShipment(): void
    {
        $methodMockery = Mockery::mock(ShippingMethod::class);
        $methodMockery->shouldReceive('getId')
                      ->twice()
                      ->withNoArgs()
                      ->andReturn(3);

        $periodMockery = Mockery::mock(ShippingPeriod::class);
        $periodMockery->shouldReceive('getId')
                      ->twice()
                      ->withNoArgs()
                      ->andReturn(2);

        $orderMockery = Mockery::mock(Order::class);
        $orderMockery->shouldReceive('getId')
                     ->twice()
                     ->withNoArgs()
                     ->andReturn(1);

        $shippingCategoryMockery = Mockery::mock(ShippingCategory::class);
        $shippingCategoryMockery->shouldReceive('getId')
                                ->twice()
                                ->withNoArgs()
                                ->andReturn(2);

        $transactionMockery = Mockery::mock(Transaction::class);

        $orderItemMock = Mockery::mock(OrderItem::class);
        $orderItemMock->shouldReceive('setOrderShipment')
                      ->once()
                      ->with(Mockery::type(OrderShipment::class))
                      ->andReturnSelf();

        $admin = Mockery::mock(Admin::class);
        $this->security->shouldReceive('getUser')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($admin);

        $sourceOrderShipment = (new OrderShipment())
            ->setIsPrinted(true)
            ->setPodCode(50000)
            ->setCategoryDeliveryRange(['2', '5'])
            ->setDeliveryDate(new DateTime("now"))
            ->setGrandTotal(100000)
            ->setDescription("test")
            ->setMethod($methodMockery)
            ->setPeriod($periodMockery)
            ->setOrder($orderMockery)
            ->setShippingCategory($shippingCategoryMockery)
            ->setTitle("FMCG")
            ->setStatus(OrderShipmentStatus::WAITING_FOR_SUPPLY)
            ->setSubTotal(120000)
            ->setTransaction($transactionMockery)
            ->addOrderItem($orderItemMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with(Mockery::type(OrderShipment::class))
                 ->andReturnNull();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        /** @var OrderShipment $targetShipment */
        $targetShipment = $this->partialOrderShipmentService->cloneShipment($sourceOrderShipment);

        self::assertEquals($sourceOrderShipment->getTitle(), $targetShipment->getTitle());
        self::assertEquals($sourceOrderShipment->getGrandTotal(), $targetShipment->getGrandTotal());
        self::assertEquals($sourceOrderShipment->getSubTotal(), $targetShipment->getSubTotal());
        self::assertEquals($sourceOrderShipment->getDescription(), $targetShipment->getDescription());
        self::assertEquals($sourceOrderShipment->getDeliveryDate(), $targetShipment->getDeliveryDate());
        self::assertEquals($sourceOrderShipment->getOrder()->getId(), $targetShipment->getOrder()->getId());
        self::assertEquals(
            $sourceOrderShipment->getShippingCategory()->getId(),
            $targetShipment->getShippingCategory()->getId()
        );
        self::assertEquals($sourceOrderShipment->getMethod()->getId(), $targetShipment->getMethod()->getId());
        self::assertEquals($sourceOrderShipment->getPeriod()->getId(), $targetShipment->getPeriod()->getId());
        self::assertEquals($sourceOrderShipment->getStatus(), $targetShipment->getStatus());
        self::assertEquals(
            $sourceOrderShipment->getCategoryDeliveryRange()[0],
            $targetShipment->getCategoryDeliveryRange()[0]
        );
        self::assertEquals(
            $sourceOrderShipment->getCategoryDeliveryRange()[1],
            $targetShipment->getCategoryDeliveryRange()[1]
        );
        self::assertEquals(false, $targetShipment->getIsPrinted());
        self::assertNull($targetShipment->getTransaction());
        self::assertEmpty($targetShipment->getOrderItems());
        self::assertNotEquals($sourceOrderShipment->getPodCode(), $targetShipment->getPodCode());
    }

    public function testItCanMoveOrderItems(): void
    {
        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('isShipmentFullyStoraged')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(true);

        $orderItem = Mockery::mock(OrderItem::class);

        $orderItem->shouldReceive('getOrderShipment')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($orderShipment);

        $orderItem->shouldReceive('setOrderShipment')
                  ->twice()
                  ->with($orderShipment)
                  ->andReturnSelf();

        $this->orderShipmentStatusServiceMock->shouldReceive('change')
                                             ->once()
                                             ->with($orderShipment, OrderShipmentStatus::WAREHOUSE)
                                             ->andReturnNull();

        $promotionDiscount = Mockery::mock(PromotionDiscount::class);
        $promotionDiscount->shouldReceive('setOrderShipment')
                          ->twice()
                          ->with($orderShipment)
                          ->andReturnSelf();

        $collection = new ArrayCollection([$promotionDiscount]);
        $orderItem->shouldReceive('getDiscounts')
                  ->twice()
                  ->withNoArgs()
                  ->andReturn($collection);

        $orderItems = new ArrayCollection([$orderItem, $orderItem]);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->partialOrderShipmentService->moveItems($orderShipment, $orderItems);
    }
}
