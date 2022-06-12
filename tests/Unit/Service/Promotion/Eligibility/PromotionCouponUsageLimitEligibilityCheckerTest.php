<?php

/**
 * User: amir
 * Date: 11/28/20
 * Time: 12:32 AM
 */

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Repository\OrderRepository;
use App\Service\Promotion\Eligibility\PromotionCouponUsageLimitEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionCouponUsageLimitEligibilityCheckerTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testIsEligibleWhenNotLimited(): void
    {
        $subject         = Mockery::mock(PromotionSubjectInterface::class);
        $promotionCoupon = Mockery::mock(PromotionCoupon::class);
        $promotionCoupon->shouldReceive('getUsageLimit')->once()->withNoArgs()->andReturnNull();
        $orderRepository = Mockery::mock(OrderRepository::class);

        $sut = new PromotionCouponUsageLimitEligibilityChecker($orderRepository);
        self::assertTrue($sut->isEligible($subject, $promotionCoupon));
    }

    public function data(): Generator
    {
        yield [10, 9, true];
        yield [10, 10, false];
        yield [10, 11, false];
    }

    /**
     * @dataProvider data
     */
    public function testIsEligibleWhenLimited($limit, $usage, $result): void
    {
        $subject         = Mockery::mock(PromotionSubjectInterface::class);
        $promotionCoupon = Mockery::mock(PromotionCoupon::class);
        $orderRepository = Mockery::mock(OrderRepository::class);

        $promotionCoupon->shouldReceive('getUsageLimit')->once()->withNoArgs()->andReturn($limit);
        $orderRepository->shouldReceive('countByCoupon')->once()->with($promotionCoupon, null)->andReturn($usage);

        $checker = new PromotionCouponUsageLimitEligibilityChecker($orderRepository);

        self::assertEquals($result, $checker->isEligible($subject, $promotionCoupon));
    }
}
