<?php

namespace App\Tests\Unit\Service\Promotion\Action;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderPromotionDiscount;
use App\Entity\OrderShipment;
use App\Entity\PromotionAction;
use App\Service\Promotion\Action\OrderItemDiscountDistributorInterface;
use App\Service\Promotion\Action\PerItemDiscountCreator;
use App\Service\Promotion\Factory\PromotionDiscountFactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PerOrderItemDiscountCreatorTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testCreateSuccessfully(): void
    {
        $action = Mockery::mock(PromotionAction::class);

        $subject = Mockery::mock(Order::class);

        $promotionDiscountOne = Mockery::mock(OrderPromotionDiscount::class);
        $promotionDiscountTwo = Mockery::mock(OrderPromotionDiscount::class);
        $subject->shouldReceive('getActionDiscounts')->once()->with($action)->andReturn(
            new ArrayCollection([$promotionDiscountOne, $promotionDiscountTwo])
        );

        $orderItemOne = Mockery::mock(OrderItem::class);
        $orderItemTwo = Mockery::mock(OrderItem::class);

        $orderItemOneQuantity = 1;
        $orderItemTwoQuantity = 1;
        $promotionDiscountOneAmount = 100;
        $promotionDiscountTwoAmount = 100;

        $orderItemOne->shouldReceive('addDiscount')->once()->with($promotionDiscountOne)->andReturnSelf();
        $orderShipment_1 = Mockery::mock(OrderShipment::class);
        $orderShipment_1->shouldReceive('addDiscount')->once()->with($promotionDiscountOne)->andReturnSelf();
        $orderItemOne->shouldReceive('getOrderShipment')->once()->withNoArgs()->andReturn($orderShipment_1);
        $orderItemOne->shouldReceive('getQuantity')->twice()->withNoArgs()->andReturn($orderItemOneQuantity);
        $promotionDiscountOne->shouldReceive('setQuantity')->once()->with($orderItemOneQuantity)->andReturnSelf();
        $promotionDiscountOne->shouldReceive('getAmount')->once()->withNoArgs()->andReturn($promotionDiscountOneAmount);
        $promotionDiscountOne
            ->shouldReceive('setUnitAmount')
            ->once()
            ->with($promotionDiscountOneAmount / $orderItemOneQuantity)
            ->andReturnSelf();

        $orderItemTwo->shouldReceive('addDiscount')->once()->with($promotionDiscountTwo)->andReturnSelf();
        $orderShipment_1 = Mockery::mock(OrderShipment::class);
        $orderShipment_1->shouldReceive('addDiscount')->once()->with($promotionDiscountTwo)->andReturnSelf();
        $orderItemTwo->shouldReceive('getOrderShipment')->once()->withNoArgs()->andReturn($orderShipment_1);
        $orderItemTwo->shouldReceive('getQuantity')->twice()->withNoArgs()->andReturn($orderItemTwoQuantity);
        $promotionDiscountTwo->shouldReceive('setQuantity')->once()->with($orderItemTwoQuantity)->andReturnSelf();
        $promotionDiscountTwo->shouldReceive('getAmount')->once()->withNoArgs()->andReturn($promotionDiscountTwoAmount);
        $promotionDiscountTwo
            ->shouldReceive('setUnitAmount')
            ->once()
            ->with($promotionDiscountTwoAmount / $orderItemTwoQuantity)
            ->andReturnSelf();

        $discountFactory = Mockery::mock(PromotionDiscountFactoryInterface::class);
        $discountFactory->shouldReceive('create')->once()->with($action, 400, $subject)->andReturn($promotionDiscountOne);
        $discountFactory->shouldReceive('create')->once()->with($action, 600, $subject)->andReturn($promotionDiscountTwo);

        $serviceLocator = Mockery::mock(ServiceLocator::class);

        $serviceLocator->shouldReceive('get')->once()->with('App\Entity\Order')->andReturn($discountFactory);

        $discountDistributor = Mockery::mock(OrderItemDiscountDistributorInterface::class);
        $discountDistributor->shouldReceive('distributeForOrder')->once()->with($subject, $action, [])->andReturn([
            ['discountAmount' => 400, 'orderItem' => $orderItemOne],
            ['discountAmount' => 600, 'orderItem' => $orderItemTwo]
        ]);

        $discountCreator = new PerItemDiscountCreator($serviceLocator, $discountDistributor);

        $context = [];
        $discounts = $discountCreator->create($action, $subject, $context);
        self::assertCount(2, $discounts);
    }
}
