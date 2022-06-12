<?php

namespace App\Tests\Unit\Service\Promotion\Action;

use App\Entity\PromotionDiscount;
use App\Service\Promotion\Action\DiscountValidation\ChainDiscountValidator;
use App\Service\Promotion\Action\DiscountValidation\ConditionalDiscountValidatorInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ChainDiscountValidatorTest extends MockeryTestCase
{
    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private LegacyMockInterface|ConditionalDiscountValidatorInterface|MockInterface|null $firstConditionalValidator;

    private LegacyMockInterface|ConditionalDiscountValidatorInterface|MockInterface|null $secondConditionalValidator;

    private LegacyMockInterface|MockInterface|PromotionDiscount|null $discount;

    private ChainDiscountValidator $chainDiscountValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject                    = Mockery::mock(PromotionSubjectInterface::class);
        $this->discount                   = Mockery::mock(PromotionDiscount::class);
        $this->firstConditionalValidator  = Mockery::mock(ConditionalDiscountValidatorInterface::class);
        $this->secondConditionalValidator = Mockery::mock(ConditionalDiscountValidatorInterface::class);

        $this->chainDiscountValidator = new ChainDiscountValidator([$this->firstConditionalValidator, $this->secondConditionalValidator]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->chainDiscountValidator);

        $this->subject                    = null;
        $this->discount                   = null;
        $this->firstConditionalValidator  = null;
        $this->secondConditionalValidator = null;

        Mockery::close();
    }

    public function testShouldApplyWillBeTrueWhenNotSupportAny()
    {
        $this->firstConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(false);
        $this->secondConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(false);


        self::assertTrue($this->chainDiscountValidator->shouldApply($this->subject));
    }

    public function testShouldApplyWillBeTrueWhenAllSupportsAndAllShouldApply()
    {
        $this->firstConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->firstConditionalValidator->shouldReceive('shouldApply')->once()->with($this->subject, [])->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('shouldApply')->once()->with($this->subject, [])->andReturn(true);

        self::assertTrue($this->chainDiscountValidator->shouldApply($this->subject));
    }

    public function testShouldApplyWillBeFalseWhenAllSupportsAndOneDoNotShouldApply()
    {
        $this->firstConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->firstConditionalValidator->shouldReceive('shouldApply')->once()->with($this->subject, [])->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('shouldApply')->once()->with($this->subject, [])->andReturn(false);

        self::assertFalse($this->chainDiscountValidator->shouldApply($this->subject));
    }

    public function testShouldRevertWillBeTrueWhenNotSupportAny()
    {
        $this->firstConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(false);
        $this->secondConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(false);
        $this->discount->shouldReceive('getSubject')->twice()->withNoArgs()->andReturn($this->subject);

        self::assertTrue($this->chainDiscountValidator->shouldRevert($this->discount));
    }

    public function testShouldRevertWillBeTrueWhenAllSupportsAndAllShouldRevert()
    {
        $this->firstConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->firstConditionalValidator->shouldReceive('shouldRevert')->once()->with($this->discount)->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('shouldRevert')->once()->with($this->discount)->andReturn(true);
        $this->discount->shouldReceive('getSubject')->twice()->withNoArgs()->andReturn($this->subject);

        self::assertTrue($this->chainDiscountValidator->shouldRevert($this->discount));
    }

    public function testShouldRevertWillBeFalseWhenAllSupportsAndOneDoNotShouldRevert()
    {
        $this->firstConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->firstConditionalValidator->shouldReceive('shouldRevert')->once()->with($this->discount)->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('supports')->once()->with($this->subject)->andReturn(true);
        $this->secondConditionalValidator->shouldReceive('shouldRevert')->once()->with($this->discount)->andReturn(false);
        $this->discount->shouldReceive('getSubject')->twice()->withNoArgs()->andReturn($this->subject);

        self::assertFalse($this->chainDiscountValidator->shouldRevert($this->discount));
    }
}
