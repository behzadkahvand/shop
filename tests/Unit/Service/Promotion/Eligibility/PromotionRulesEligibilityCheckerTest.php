<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Entity\PromotionRule;
use App\Service\Promotion\Eligibility\PromotionRulesEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\RuleTypeInterface;
use App\Service\Promotion\Rule\RuleTypeRegistryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionRulesEligibilityCheckerTest extends MockeryTestCase
{
    private Mockery\LegacyMockInterface|RuleTypeRegistryInterface|Mockery\MockInterface|null $ruleTypeRegistry;

    private Mockery\LegacyMockInterface|PromotionSubjectInterface|Mockery\MockInterface|null $subject;

    private Promotion|Mockery\LegacyMockInterface|Mockery\MockInterface|null $promotion;

    private PromotionRulesEligibilityChecker $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ruleTypeRegistry = Mockery::mock(RuleTypeRegistryInterface::class);
        $this->subject          = Mockery::mock(PromotionSubjectInterface::class);
        $this->promotion        = Mockery::mock(Promotion::class);

        $this->sut = new PromotionRulesEligibilityChecker($this->ruleTypeRegistry);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->ruleTypeRegistry = null;
        $this->subject          = null;
        $this->promotion        = null;

        Mockery::close();
    }

    public function testIsEligibleWithoutRules(): void
    {
        $this->promotion->shouldReceive('getRules')->withNoArgs()->once()->andReturn(new ArrayCollection());

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testShouldBeEligibleIfAllRulesAreValid(): void
    {
        $ruleOne = Mockery::mock(PromotionRule::class);
        $ruleOne->shouldReceive('getType')->times(3)->withNoArgs()->andReturn('rule_type_one');
        $ruleOne->shouldReceive('getConfiguration')->once()->withNoArgs()->andReturn([]);

        $ruleTwo = Mockery::mock(PromotionRule::class);
        $ruleTwo->shouldReceive('getType')->times(3)->withNoArgs()->andReturn('rule_type_two');
        $ruleTwo->shouldReceive('getConfiguration')->once()->withNoArgs()->andReturn([]);

        $ruleTypeOne = Mockery::mock(RuleTypeInterface::class);
        $ruleTypeOne->shouldReceive('isValid')->once()->with($this->subject, [], [])->andReturn(true);

        $ruleTypeTwo = Mockery::mock(RuleTypeInterface::class);
        $ruleTypeTwo->shouldReceive('isValid')->once()->with($this->subject, [], [])->andReturn(true);

        $this->ruleTypeRegistry->shouldReceive('getRuleTypeNames')->once()->withNoArgs()->andReturn(['rule_type_one', 'rule_type_two']);
        $this->ruleTypeRegistry->shouldReceive('get')->once()->with('rule_type_one')->andReturn($ruleTypeOne);
        $this->ruleTypeRegistry->shouldReceive('get')->once()->with('rule_type_two')->andReturn($ruleTypeTwo);

        $this->promotion->shouldReceive('getRules')->twice()->withNoArgs()->andReturn(new ArrayCollection([
            $ruleOne,
            $ruleTwo,
        ]));

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testShouldNotBeEligibleIfOneOfTheRulesIsInvalid(): void
    {
        $ruleOne = Mockery::mock(PromotionRule::class);
        $ruleOne->shouldReceive('getType')->times(3)->withNoArgs()->andReturn('rule_type_one');
        $ruleOne->shouldReceive('getConfiguration')->once()->withNoArgs()->andReturn([]);

        $ruleTwo = Mockery::mock(PromotionRule::class);
        $ruleTwo->shouldReceive('getType')->times(3)->withNoArgs()->andReturn('rule_type_two');
        $ruleTwo->shouldReceive('getConfiguration')->once()->withNoArgs()->andReturn([]);

        $ruleTypeOne = Mockery::mock(RuleTypeInterface::class);
        $ruleTypeOne->shouldReceive('isValid')->once()->with($this->subject, [], [])->andReturn(true);

        $ruleTypeTwo = Mockery::mock(RuleTypeInterface::class);
        $ruleTypeTwo->shouldReceive('isValid')->once()->with($this->subject, [], [])->andReturn(false);

        $this->ruleTypeRegistry->shouldReceive('getRuleTypeNames')->once()->withNoArgs()->andReturn(['rule_type_one', 'rule_type_two']);
        $this->ruleTypeRegistry->shouldReceive('get')->once()->with('rule_type_one')->andReturn($ruleTypeOne);
        $this->ruleTypeRegistry->shouldReceive('get')->once()->with('rule_type_two')->andReturn($ruleTypeTwo);

        $this->promotion->shouldReceive('getRules')->twice()->withNoArgs()->andReturn(new ArrayCollection([
            $ruleOne,
            $ruleTwo,
        ]));

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testShouldSkipOtherRulesIfOneOfTheRulesFailed(): void
    {
        $ruleOne = Mockery::mock(PromotionRule::class);
        $ruleOne->shouldReceive('getType')->times(3)->withNoArgs()->andReturn('rule_type_one');
        $ruleOne->shouldReceive('getConfiguration')->once()->withNoArgs()->andReturn([]);

        $ruleTwo = Mockery::mock(PromotionRule::class);
        $ruleTwo->shouldReceive('getType')->times(2)->withNoArgs()->andReturn('rule_type_two');

        $ruleTypeOne = Mockery::mock(RuleTypeInterface::class);
        $ruleTypeOne->shouldReceive('isValid')->once()->with($this->subject, [], [])->andReturn(false);

        $this->ruleTypeRegistry->shouldReceive('getRuleTypeNames')->once()->withNoArgs()->andReturn(['rule_type_one', 'rule_type_two']);
        $this->ruleTypeRegistry->shouldReceive('get')->once()->with('rule_type_one')->andReturn($ruleTypeOne);

        $this->promotion->shouldReceive('getRules')->twice()->withNoArgs()->andReturn(new ArrayCollection([
            $ruleOne,
            $ruleTwo,
        ]));

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotion));
    }

    public function testIsEligibleFailBecauseOfInvalidRule(): void
    {
        $ruleOne = Mockery::mock(PromotionRule::class);
        $ruleOne->shouldReceive('getType')->times(1)->withNoArgs()->andReturn('rule_type_one');

        $this->ruleTypeRegistry->shouldReceive('getRuleTypeNames')->once()->withNoArgs()->andReturn([]);

        $this->promotion->shouldReceive('getRules')->twice()->withNoArgs()->andReturn(new ArrayCollection([$ruleOne]));

        self::expectException(InvalidArgumentException::class);
        $this->sut->isEligible($this->subject, $this->promotion);
    }
}
