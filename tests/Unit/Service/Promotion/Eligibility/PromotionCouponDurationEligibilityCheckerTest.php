<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\Eligibility\PromotionCouponDurationEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionCouponDurationEligibilityCheckerTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testIsEligible(): void
    {
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $subject                 = Mockery::mock(PromotionSubjectInterface::class);
        $promotionCoupon         = Mockery::mock(PromotionCoupon::class);

        $promotionCoupon->shouldReceive('getExpiresAt')->once()->withNoArgs()->andReturn(new DateTime('+1 minute'));

        $sut = new PromotionCouponDurationEligibilityChecker($contextOperationManager);

        self::assertTrue($sut->isEligible($subject, $promotionCoupon));
    }

    public function testIsNotEligible(): void
    {
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $subject                 = Mockery::mock(PromotionSubjectInterface::class);
        $promotionCoupon         = Mockery::mock(PromotionCoupon::class);

        $contextOperationManager
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with([], 'این کد تخفیف منقضی شده است.')
            ->andReturnNull();

        $promotionCoupon->shouldReceive('getExpiresAt')->once()->withNoArgs()->andReturn(new DateTime('-1 minute'));

        $sut = new PromotionCouponDurationEligibilityChecker($contextOperationManager);

        self::assertFalse($sut->isEligible($subject, $promotionCoupon));
    }
}
