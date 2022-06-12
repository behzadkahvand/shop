<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Brand\Admin;

use App\Service\ORM\CustomFilters\Brand\MultiColumnSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class MultiColumnSearchCustomFilterTest
 */
final class MultiColumnSearchCustomFilterTest extends FunctionalTestCase
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

    public function testItReturnIfBrandFilterIsNotSet()
    {
        $customFilter = new MultiColumnSearchCustomFilter(\Mockery::mock(EventDispatcherInterface::class));
        $reqeust      = new Request();

        $customFilter->apply($reqeust);

        self::assertEquals([], $reqeust->query->all());
    }

    public function testItApplyCustomFilter()
    {
        $brandName = 'brand name';

        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
        $customFilter = new MultiColumnSearchCustomFilter($dispatcher);
        $reqeust      = new Request([
            'filter' => [
                'brand'    => ['like' => $brandName],
                'title'    => 'brand title',
                'subtitle' => 'brand subtitle',
            ],
        ]);

        $customFilter->apply($reqeust);

        self::assertEquals(['filter' => []], $reqeust->query->all());

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('setParameter')->once()->with('brand', "{$brandName}%")->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with('brand.title LIKE :brand OR brand.subtitle LIKE :brand')
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent($queryBuilder, \Mockery::mock(QueryContext::class), 'brand', []);

        $dispatcher->dispatch($event);
    }
}
