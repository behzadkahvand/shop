<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Seller\Admin;

use App\Service\ORM\CustomFilters\Seller\Admin\MultiColumnSearchCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    public function testItReturnIfSellerFilterIsNotSet()
    {
        $customFilter = new MultiColumnSearchCustomFilter(\Mockery::mock(EventDispatcherInterface::class));
        $reqeust      = new Request();

        $customFilter->apply($reqeust);

        self::assertEquals([], $reqeust->query->all());
    }

    public function testItApplyCustomFilter()
    {
        $seller = 'seller';

        $dispatcher   = self::getContainer()->get(EventDispatcherInterface::class);
        $customFilter = new MultiColumnSearchCustomFilter($dispatcher);
        $reqeust      = new Request([
            'filter' => [
                'seller'         => ['like' => $seller],
                'identifier'     => 1,
                'name'           => 'seller name',
                'nationalNumber' => '123456',
                'nationalCode'   => '654321',
            ],
        ]);

        $customFilter->apply($reqeust);

        self::assertEquals(['filter' => []], $reqeust->query->all());

        $expression  = '%1$s.identifier = :seller OR ';
        $expression .= '%1$s.nationalNumber = :seller OR ';
        $expression .= '%1$s.nationalIdentifier = :seller OR ';
        $expression .= '%1$s.name like :seller_name';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('setParameter')->once()->with('seller', $seller)->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')->once()->with('seller_name', "%{$seller}%")->andReturnSelf();
        $queryBuilder->shouldReceive('andWhere')
                     ->once()
                     ->with(sprintf($expression, 'seller'))
                     ->andReturnSelf();

        $event = new QueryBuilderFilterAppliedEvent($queryBuilder, \Mockery::mock(QueryContext::class), 'seller', []);

        $dispatcher->dispatch($event);
    }
}
