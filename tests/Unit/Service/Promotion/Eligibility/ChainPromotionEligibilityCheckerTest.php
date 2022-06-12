<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\Eligibility\ChainPromotionEligibilityChecker;
use App\Service\Promotion\Eligibility\PromotionEligibilityCheckerInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ChainPromotionEligibilityCheckerTest extends MockeryTestCase
{
    private Mockery\LegacyMockInterface|Mockery\MockInterface|PromotionCoupon|null $promotion;

    private Mockery\LegacyMockInterface|PromotionSubjectInterface|Mockery\MockInterface|null $subject;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|PromotionEligibilityCheckerInterface|null $eligibilityCheckerOne;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|PromotionEligibilityCheckerInterface|null $eligibilityCheckerTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotion             = Mockery::mock(Promotion::class);
        $this->subject               = Mockery::mock(PromotionSubjectInterface::class);
        $this->eligibilityCheckerOne = Mockery::mock(PromotionEligibilityCheckerInterface::class);
        $this->eligibilityCheckerTwo = Mockery::mock(PromotionEligibilityCheckerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->promotion             = null;
        $this->subject               = null;
        $this->eligibilityCheckerOne = null;
        $this->eligibilityCheckerTwo = null;

        Mockery::close();
    }

    public function testIsEligibleFalse(): void
    {
        $this->eligibilityCheckerOne->shouldReceive('isEligible')->with($this->subject, $this->promotion, [])->once()->andReturn(false);

        $sut = new ChainPromotionEligibilityChecker([$this->eligibilityCheckerOne, $this->eligibilityCheckerTwo]);

        self::assertFalse($sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsEligibleTrue(): void
    {
        $this->eligibilityCheckerOne->shouldReceive('isEligible')->with($this->subject, $this->promotion, [])->once()->andReturn(true);
        $this->eligibilityCheckerTwo->shouldReceive('isEligible')->with($this->subject, $this->promotion, [])->once()->andReturn(true);

        $sut = new ChainPromotionEligibilityChecker([$this->eligibilityCheckerOne, $this->eligibilityCheckerTwo]);

        self::assertTrue($sut->isEligible($this->subject, $this->promotion));
    }
}
