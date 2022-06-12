<?php

namespace App\Tests\Unit\Service\Promotion\Rule;

use App\Repository\CategoryRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\CategoryRuleType;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\AbstractType;

class CategoryRuleTypeTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function data(): Generator
    {
        yield [[1 => [10, 11], 2 => [12, 13]], ['category_ids' => [2, 3]], true, [12, 13]];
        yield [[1 => [10, 11], 2 => [12, 13]], ['category_ids' => [3, 4]], false, null];
    }

    /**
     * @dataProvider data
     */
    public function testIsValid($subjectCategoryIds, $configurations, $result): void
    {
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        if (!$result) {
            $contextOperationManager
                ->shouldReceive('addErrorMessage')
                ->once()
                ->with([], 'این کد تخفیف تنها بر روی دسته بندی های محدودی قابل استفاده است. ')
                ->andReturnNull();
        }

        $categoryRepository = Mockery::mock(CategoryRepository::class);
        $categoryRepository
            ->shouldReceive('getCategoryLeafIdsForCategoryIds')
            ->once()
            ->with(...$configurations['category_ids'])
            ->andReturn($configurations['category_ids']);

        $subject = Mockery::mock(PromotionSubjectInterface::class);
        $collection = Mockery::mock(ArrayCollection::class);
        $subject->shouldReceive('getItems')->withNoArgs()->andReturn($collection);
        $categoryRepository
            ->shouldReceive('getCategoryIdsFromItemCollection')
            ->once()
            ->with($collection)
            ->andReturn($subjectCategoryIds);

        $rule = new CategoryRuleType($categoryRepository, $contextOperationManager);

        $context = [];
        self::assertEquals($result, $rule->isValid($subject, $configurations, $context));
        if ($result) {
            self::assertEquals([12, 13], $context['inventory_ids']);
        } else {
            self::assertEquals([], $context);
        }
    }

    public function testGetName(): void
    {
        self::assertIsString(CategoryRuleType::getName());
    }

    public function testConfigurationFormType(): void
    {
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $categoryRepository = Mockery::mock(CategoryRepository::class);
        $ruleType = new CategoryRuleType($categoryRepository, $contextOperationManager);
        self::assertTrue(is_subclass_of($ruleType->getConfigurationFormType(), AbstractType::class));
    }
}
