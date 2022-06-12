<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\Eligibility\PromotionDurationEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionDurationEligibilityCheckerTest extends MockeryTestCase
{
    private ContextOperationManager|Mockery\LegacyMockInterface|Mockery\MockInterface|null $contextOperationManager;

    private Mockery\LegacyMockInterface|PromotionSubjectInterface|Mockery\MockInterface|null $subject;

    private Promotion|Mockery\LegacyMockInterface|Mockery\MockInterface|null $promotion;

    private PromotionDurationEligibilityChecker $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $this->subject                 = Mockery::mock(PromotionSubjectInterface::class);
        $this->promotion               = Mockery::mock(Promotion::class);

        $this->sut = new PromotionDurationEligibilityChecker($this->contextOperationManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->contextOperationManager = null;
        $this->subject                 = null;
        $this->promotion               = null;

        Mockery::close();
    }

    public function testIsEligibleWhenStartsAndEndsAtAreNull(): void
    {
        $this->promotion->shouldReceive('getStartsAt')->once()->withNoArgs()->andReturnNull();
        $this->promotion->shouldReceive('getEndsAt')->once()->withNoArgs()->andReturnNull();

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsEligibleWhenStartsAtIsSetAndNotReached(): void
    {
        $this->promotion->shouldReceive('getStartsAt')->once()->withNoArgs()->andReturn(new DateTime('+1 minute'));

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsEligibleWhenEndsAtIsSetAndPassed(): void
    {
        $this->promotion->shouldReceive('getStartsAt')->once()->withNoArgs()->andReturnNull();
        $this->promotion->shouldReceive('getEndsAt')->once()->withNoArgs()->andReturn(new DateTime('-1 minute'));
        $this->contextOperationManager
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with([], 'این کد تخفیف منقضی شده است.')
            ->andReturnNull();

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsEligibleWhenStartsAtAndEndsAtIsSetAndReachedAndNotPassed(): void
    {
        $this->promotion->shouldReceive('getStartsAt')->once()->withNoArgs()->andReturn(new DateTime('-1 minute'));
        $this->promotion->shouldReceive('getEndsAt')->once()->withNoArgs()->andReturn(new DateTime('+1 minute'));

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotion));
    }
}
