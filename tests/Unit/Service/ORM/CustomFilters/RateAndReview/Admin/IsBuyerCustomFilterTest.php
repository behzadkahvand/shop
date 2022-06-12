<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\RateAndReview\Admin;

use App\Service\ORM\CustomFilters\RateAndReview\Admin\IsBuyerCustomFilter;
use App\Service\ORM\Events\QueryBuilderFilterAppliedEvent;
use App\Service\ORM\QueryContext;
use App\Tests\Integration\BaseIntegrationTestCase;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class IsBuyerCustomFilterTest extends BaseUnitTestCase
{
    private EventDispatcher|null $dispatcher;
    private QueryBuilder|LegacyMockInterface|MockInterface|null $queryBuilder;
    private QueryBuilderFilterAppliedEvent|null $event;
    private IsBuyerCustomFilter|null $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher   = new EventDispatcher();
        $this->queryBuilder = Mockery::mock(QueryBuilder::class);
        $this->event = new QueryBuilderFilterAppliedEvent(
            $this->queryBuilder,
            Mockery::mock(QueryContext::class),
            'rate_and_review',
            []
        );
        $this->sut = new IsBuyerCustomFilter($this->dispatcher);
    }

    public function testShouldDoNothingWhenIsBuyerFilterIsNotSet(): void
    {
        $query = [
            'filter' => [
                'param_1' => 'dummy value',
                'param_2' => 'another dummy value',
            ],
        ];
        $request      = new Request($query);

        $this->sut->apply($request);

        self::assertEquals($query, $request->query->all());
    }

    public function testShouldWorkWhenIsBuyerFilterIsSetToTrue(): void
    {
        $query = ['filter' => ['param' => 'value', 'isBuyer' => true]];
        $request      = new Request($query);

        $this->sut->apply($request);

        $expected = ['filter' => ['param' => 'value']];
        self::assertEquals($expected, $request->query->all());

        $this->queryBuilder->shouldReceive('AndWhere')
                     ->once()
                     ->with('rate_and_review.order IS NOT NULL')
                     ->andReturnSelf();

        $this->dispatcher->dispatch($this->event);
    }

    public function testShouldWorkWhenIsBuyerFilterIsSetToFalse(): void
    {
        $query = ['filter' => ['param' => 'value', 'isBuyer' => false]];
        $request      = new Request($query);

        $this->sut->apply($request);

        $expected = ['filter' => ['param' => 'value']];
        self::assertEquals($expected, $request->query->all());

        $this->queryBuilder->shouldReceive('AndWhere')
                     ->once()
                     ->with('rate_and_review.order IS NULL')
                     ->andReturnSelf();

        $this->dispatcher->dispatch($this->event);
    }
}
