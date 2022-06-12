<?php

namespace App\Tests\Unit\Service\Promotion\Rule;

use App\Service\Promotion\Rule\RuleTypeInterface;
use App\Service\Promotion\Rule\RuleTypeRegistry;
use ArrayObject;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RuleTypeRegistryTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testGetNotExistingRuleType(): void
    {
        $ruleTypeOne = Mockery::mock(RuleTypeInterface::class);

        $ruleTypeRegistry = new RuleTypeRegistry(new ArrayObject(['rule_type_one' => $ruleTypeOne]));
        self::assertNull($ruleTypeRegistry->get('non_existing_rule_type'));
    }

    public function testGetExistingRuleType(): void
    {
        $ruleTypeOne = Mockery::mock(RuleTypeInterface::class);

        $ruleTypeRegistry = new RuleTypeRegistry(new ArrayObject(['rule_type_one' => $ruleTypeOne]));
        self::assertEquals($ruleTypeOne, $ruleTypeRegistry->get('rule_type_one'));
    }

    public function testGetRuleTypeNames(): void
    {
        $ruleTypeOne = Mockery::mock(RuleTypeInterface::class);

        $ruleTypeRegistry = new RuleTypeRegistry(new ArrayObject(['rule_type_one' => $ruleTypeOne]));
        $ruleTypeNames    = $ruleTypeRegistry->getRuleTypeNames();
        self::assertCount(1, $ruleTypeNames);
        self::assertContains('rule_type_one', $ruleTypeNames);
    }
}
