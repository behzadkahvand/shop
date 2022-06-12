<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\Eligibility\PromotionCouponEligibilityCheckerInterface;
use App\Service\Promotion\Eligibility\PromotionSubjectCouponEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PromotionSubjectCouponEligibilityCheckerTest extends MockeryTestCase
{
    private Promotion|LegacyMockInterface|MockInterface|null $promotion;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private LegacyMockInterface|PromotionCouponEligibilityCheckerInterface|MockInterface|null $promotionCouponEligibilityChecker;

    private PromotionSubjectCouponEligibilityChecker $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotion                         = Mockery::mock(Promotion::class);
        $this->subject                           = Mockery::mock(PromotionSubjectInterface::class);
        $this->promotionCouponEligibilityChecker = Mockery::mock(PromotionCouponEligibilityCheckerInterface::class);

        $this->sut = new PromotionSubjectCouponEligibilityChecker($this->promotionCouponEligibilityChecker);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->promotion                         = null;
        $this->subject                           = null;
        $this->promotionCouponEligibilityChecker = null;

        Mockery::close();
    }

    public function testIsEligibleWhenNotCouponBased(): void
    {
        $this->promotion->shouldReceive('getCouponBased')->once()->withNoArgs()->andReturnFalse();

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsNotEligibleWhenSubjectDoesNotHaveCoupon(): void
    {
        $this->promotion->shouldReceive('getCouponBased')->once()->withNoArgs()->andReturnTrue();

        $this->subject->shouldReceive('getPromotionCoupon')->once()->withNoArgs()->andReturnNull();

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsNotEligibleWhenSubjectCouponPromotionDoesNotMatch(): void
    {
        $this->promotion->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->promotion->shouldReceive('getCouponBased')->once()->withNoArgs()->andReturnTrue();

        $promotionOfCoupon = Mockery::mock(Promotion::class);
        $promotionOfCoupon->shouldReceive('getId')->once()->withNoArgs()->andReturn(2);

        $coupon = Mockery::mock(PromotionCoupon::class);
        $coupon->shouldReceive('getPromotion')->once()->withNoArgs()->andReturn($promotionOfCoupon);

        $this->subject->shouldReceive('getPromotionCoupon')->once()->withNoArgs()->andReturn($coupon);

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsNotEligibleBecauseCouponIsNotEligible(): void
    {
        $this->promotion->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1);
        $this->promotion->shouldReceive('getCouponBased')->once()->withNoArgs()->andReturnTrue();

        $coupon = Mockery::mock(PromotionCoupon::class);
        $coupon->shouldReceive('getPromotion')->once()->withNoArgs()->andReturn($this->promotion);

        $this->subject->shouldReceive('getPromotionCoupon')->once()->withNoArgs()->andReturn($coupon);

        $this->promotionCouponEligibilityChecker
            ->shouldReceive('isEligible')
            ->once()
            ->with($this->subject, $coupon, [])
            ->andReturnFalse();

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsEligible(): void
    {
        $this->promotion->shouldReceive('getId')->twice()->withNoArgs()->andReturn(1);
        $this->promotion->shouldReceive('getCouponBased')->once()->withNoArgs()->andReturnTrue();

        $coupon = Mockery::mock(PromotionCoupon::class);
        $coupon->shouldReceive('getPromotion')->once()->withNoArgs()->andReturn($this->promotion);

        $this->subject->shouldReceive('getPromotionCoupon')->once()->withNoArgs()->andReturn($coupon);

        $this->promotionCouponEligibilityChecker
            ->shouldReceive('isEligible')
            ->once()
            ->with($this->subject, $coupon, [])
            ->andReturnTrue();

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotion));
    }
}
