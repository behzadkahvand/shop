<?php

/**
 * User: amir
 * Date: 11/28/20
 * Time: 1:23 AM
 */

namespace App\Tests\Unit\Service\Promotion\Factory;

use App\Entity\Cart;
use App\Entity\CartPromotionDiscount;
use App\Entity\Order;
use App\Entity\OrderPromotionDiscount;
use App\Entity\PromotionAction;
use App\Service\Promotion\Factory\CartPromotionDiscountFactory;
use App\Service\Promotion\Factory\OrderPromotionDiscountFactory;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionDiscountFactoryTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testSupportedSubjectClassForCartPromotionDiscountFactory(): void
    {
        $supportedSubject = CartPromotionDiscountFactory::supportedSubjectClass();
        self::assertTrue(
            is_subclass_of($supportedSubject, Cart::class) || $supportedSubject === Cart::class
        );
    }

    public function testSupportedSubjectClassForOrderPromotionDiscountFactory(): void
    {
        $supportedSubject = OrderPromotionDiscountFactory::supportedSubjectClass();
        self::assertTrue(
            is_subclass_of($supportedSubject, Order::class) || $supportedSubject === Order::class
        );
    }

    public function testCreateForOrderPromotionDiscountFactory(): void
    {
        $entityManager = Mockery::mock(EntityManager::class);
        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(OrderPromotionDiscount::class);

        $promotionAction = Mockery::mock(PromotionAction::class);
        $subject         = Mockery::mock(Order::class);
        $subject
            ->shouldReceive('addDiscount')
            ->once()
            ->with(OrderPromotionDiscount::class);

        $sut      = new OrderPromotionDiscountFactory($entityManager);
        $discount = $sut->create($promotionAction, 1000, $subject);
        self::assertTrue($discount instanceof OrderPromotionDiscount);
    }

    public function testCreateForCartPromotionDiscountFactory(): void
    {
        $entityManager = Mockery::mock(EntityManager::class);
        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(CartPromotionDiscount::class);

        $promotionAction = Mockery::mock(PromotionAction::class);
        $subject         = Mockery::mock(Cart::class);
        $subject
            ->shouldReceive('addDiscount')
            ->once()
            ->with(CartPromotionDiscount::class);

        $sut      = new CartPromotionDiscountFactory($entityManager);
        $discount = $sut->create($promotionAction, 1000, $subject);
        self::assertTrue($discount instanceof CartPromotionDiscount);
    }
}
