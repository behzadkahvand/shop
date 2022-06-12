<?php

namespace App\Tests\Unit\Service\Promotion\Rule;

use App\Repository\ProductRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\ProductRuleType;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Form\AbstractType;

class ProductRuleTypeTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function data(): Generator
    {
        yield [[1 => [11, 12], 2 => [13, 14]], ['product_ids' => [2, 3]], true];
        yield [[1 => [11, 12], 2 => [13, 14]], ['product_ids' => [3, 4]], false];
    }

    /**
     * @dataProvider data
     */
    public function testIsValid($subjectProductIds, $configurations, $result): void
    {
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        if ($result) {
            $contextOperationManager->shouldNotReceive('addErrorMessage');
        } else {
            $contextOperationManager
                ->shouldReceive('addErrorMessage')
                ->with([], 'این کد تخفیف تنها بر روی کالاهای محدودی قابل استفاده است. ')
                ->once()->andReturnNull();
        }

        $collection = Mockery::mock(ArrayCollection::class);

        $productRepository = Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('getProductIdsFromItemCollection')->once()->with($collection)->andReturn($subjectProductIds);

        $subject = Mockery::mock(PromotionSubjectInterface::class);
        $subject->shouldReceive('getItems')->once()->withNoArgs()->andReturn($collection);

        $rule = new ProductRuleType($productRepository, $contextOperationManager);

        $context = [];
        self::assertEquals($result, $rule->isValid($subject, $configurations, $context));
        if ($result) {
            self::assertEquals([13, 14], $context['inventory_ids']);
        } else {
            self::assertEquals([], $context);
        }
    }

    public function testGetName(): void
    {
        self::assertIsString(ProductRuleType::getName());
    }

    public function testConfigurationFormType(): void
    {
        $contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $productRepository       = Mockery::mock(ProductRepository::class);
        $ruleType                = new ProductRuleType($productRepository, $contextOperationManager);
        self::assertTrue(is_subclass_of($ruleType->getConfigurationFormType(), AbstractType::class));
    }
}
