<?php

namespace App\Tests\Unit\Service\Promotion\Rule;

use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\MinimumBasketTotalRuleType;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\AbstractType;

class MinimumBasketTotalRuleTypeTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function data(): Generator
    {
        yield [[], 100000, false];
        yield [['basket_total' => 'hundred'], 100000, false];
        yield [['basket_total' => 100001], 100000, false];
        yield [['basket_total' => 100000], 100000, true];
        yield [['basket_total' => 99999], 100000, true];
    }

    /**
     * @dataProvider data
     */
    public function testIsValid($configuration, $total, $result): void
    {
        $entityManager           = Mockery::mock(EntityManagerInterface::class);
        $subject                 = Mockery::mock(PromotionSubjectInterface::class);
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        if (!$result && !empty($configuration) && is_int($configuration['basket_total'])) {
            $contextOperationManager
                ->shouldReceive('addErrorMessage')
                ->with([], 'حداقل میزان سبد خرید رعایت نشده است. ')
                ->once()->andReturnNull();
        } else {
            $contextOperationManager->shouldNotReceive('addErrorMessage');
        }

        if (!empty($configuration) && is_int($configuration['basket_total'])) {
            $subject->shouldReceive('getPromotionSubjectTotal')->once()->withNoArgs()->andReturn($total);
        }

        $rule = new MinimumBasketTotalRuleType($entityManager, $contextOperationManager);

        self::assertEquals($result, $rule->isValid($subject, $configuration));
    }

    public function testGetName(): void
    {
        self::assertIsString(MinimumBasketTotalRuleType::getName());
    }

    public function testConfigurationFormType(): void
    {
        $entityManager           = Mockery::mock(EntityManagerInterface::class);
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $ruleType                = new MinimumBasketTotalRuleType($entityManager, $contextOperationManager);
        self::assertTrue(is_subclass_of($ruleType->getConfigurationFormType(), AbstractType::class));
    }
}
