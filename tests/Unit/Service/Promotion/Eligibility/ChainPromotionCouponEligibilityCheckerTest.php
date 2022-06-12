<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\PromotionCoupon;
use App\Service\Promotion\Eligibility\ChainPromotionCouponEligibilityChecker;
use App\Service\Promotion\Eligibility\PromotionCouponEligibilityCheckerInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ChainPromotionCouponEligibilityCheckerTest extends MockeryTestCase
{
    private LegacyMockInterface|MockInterface|PromotionCoupon|null $promotionCoupon;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private LegacyMockInterface|PromotionCouponEligibilityCheckerInterface|MockInterface|null $eligibilityCheckerOne;

    private LegacyMockInterface|PromotionCouponEligibilityCheckerInterface|MockInterface|null $eligibilityCheckerTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionCoupon       = Mockery::mock(PromotionCoupon::class);
        $this->subject               = Mockery::mock(PromotionSubjectInterface::class);
        $this->eligibilityCheckerOne = Mockery::mock(PromotionCouponEligibilityCheckerInterface::class);
        $this->eligibilityCheckerTwo = Mockery::mock(PromotionCouponEligibilityCheckerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->promotionCoupon       = null;
        $this->subject               = null;
        $this->eligibilityCheckerOne = null;
        $this->eligibilityCheckerTwo = null;

        Mockery::close();
    }

    public function testIsEligibleFalse(): void
    {
        $this->eligibilityCheckerOne->shouldReceive('isEligible')->with($this->subject, $this->promotionCoupon, [])->once()->andReturn(false);

        $chainEligibilityChecker = new ChainPromotionCouponEligibilityChecker([$this->eligibilityCheckerOne, $this->eligibilityCheckerTwo]);

        self::assertFalse($chainEligibilityChecker->isEligible($this->subject, $this->promotionCoupon));
    }

    public function testIsEligibleTrue(): void
    {
        $this->eligibilityCheckerOne->shouldReceive('isEligible')->with($this->subject, $this->promotionCoupon, [])->once()->andReturn(true);
        $this->eligibilityCheckerTwo->shouldReceive('isEligible')->with($this->subject, $this->promotionCoupon, [])->once()->andReturn(true);

        $chainEligibilityChecker = new ChainPromotionCouponEligibilityChecker([$this->eligibilityCheckerOne, $this->eligibilityCheckerTwo]);

        self::assertTrue($chainEligibilityChecker->isEligible($this->subject, $this->promotionCoupon));
    }
}
