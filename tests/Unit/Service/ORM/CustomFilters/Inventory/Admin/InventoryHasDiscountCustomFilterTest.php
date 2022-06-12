<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Inventory\Admin;

use App\Service\ORM\CustomFilters\Inventory\Admin\InventoryHasDiscountCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class InventoryHasDiscountCustomFilterTest
 */
final class InventoryHasDiscountCustomFilterTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    public function testItReturnIfHasDiscountFilterIsNotSet()
    {
        $request      = new Request();
        $customFilter = self::getContainer()->get(InventoryHasDiscountCustomFilter::class);

        $customFilter->apply($request);

        self::assertEquals([], $request->query->all());
    }

    public function testItApplyHasDiscountFilter()
    {
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $request = new Request(['filter' => ['hasDiscount' => true]]);

        /** @var InventoryHasDiscountCustomFilter $customFilter */
        $customFilter = self::getContainer()->get(InventoryHasDiscountCustomFilter::class);

        $customFilter->apply($request);

        self::assertEquals(['filter' => []], $request->query->all());

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with('inventory.price != inventory.finalPrice')
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent(
            $queryBuilder,
            \Mockery::mock(QueryContext::class),
            'inventory',
            []
        );

        $dispatcher->dispatch($event);
    }

    public function testItApplyHasNotDiscountFilter()
    {
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $request = new Request(['filter' => ['hasDiscount' => false]]);

        /** @var InventoryHasDiscountCustomFilter $customFilter */
        $customFilter = self::getContainer()->get(InventoryHasDiscountCustomFilter::class);

        $customFilter->apply($request);

        self::assertEquals(['filter' => []], $request->query->all());

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with('inventory.price = inventory.finalPrice')
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent(
            $queryBuilder,
            \Mockery::mock(QueryContext::class),
            'inventory',
            []
        );

        $dispatcher->dispatch($event);
    }
}
