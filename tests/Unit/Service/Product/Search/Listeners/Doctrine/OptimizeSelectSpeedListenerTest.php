<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Events\Product\Search\SellerProductSearchQueryEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\OptimizeSelectSpeedListener;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\Pagination\PaginatorUtils;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OptimizeSelectSpeedListenerTest extends MockeryTestCase
{
    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    /**
     * @var AbstractQuery|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryMock;

    /**
     * @var PaginatorUtils|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $paginatorUtilsMock;

    /**
     * @var Paginator|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $paginatorMock;

    protected OptimizeSelectSpeedListener $optimizeSelectSpeedListener;

    protected function setUp(): void
    {
        parent::setUp();

        $em                       = Mockery::mock(EntityManagerInterface::class);
        $this->queryBuilderMock   = Mockery::mock(QueryBuilder::class, [$em]);
        $this->queryMock          = Mockery::mock(AbstractQuery::class);
        $this->paginatorUtilsMock = Mockery::mock(PaginatorUtils::class);
        $this->paginatorMock      = Mockery::mock(Paginator::class);

        $this->optimizeSelectSpeedListener = new OptimizeSelectSpeedListener($this->paginatorUtilsMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->queryBuilderMock,
            $this->queryMock,
            $this->optimizeSelectSpeedListener
        );
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->optimizeSelectSpeedListener::getSubscribedEvents();

        self::assertEquals([
            ProductSearchQueryEvent::class       => ['onProductSearchQueryEvent', 1],
        ], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            'invalid',
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        self::assertNull($this->optimizeSelectSpeedListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());
    }

    public function testItDoNothingWhenPriceSortIsSet()
    {
        $data       = new SearchData([], ['-price']);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        self::assertNull($this->optimizeSelectSpeedListener->onProductSearchQueryEvent($event));

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());
    }

    public function testItCanOptimizeSelectSpeedWhenItHasProductIds()
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination(2, 30);

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $rootAlias  = 'root_alias';
        $productIds = [1, 2, 4];

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("PARTIAL {$rootAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with(30)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with(30)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('distinct')
                               ->once()
                               ->with(true)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getAllAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(['product1cb', 'BuyBox']);
        $this->queryBuilderMock->shouldReceive('getDQLPart')
                               ->once()
                               ->with('select')
                               ->andReturn([
                                   new Select('product1cb'),
                                   new Select('BuyBox'),
                                   new Select('CASE WHEN BuyBox.finalPrice < BuyBox.price THEN 1 ELSE 0 END AS HIDDEN promotionSort')
                               ]);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('CASE WHEN BuyBox.finalPrice < BuyBox.price THEN 1 ELSE 0 END AS HIDDEN promotionSort')
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id IN (:productIds)")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with(compact('productIds'))
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('orderBy')
                               ->once()
                               ->with(sprintf('FIELD(%s.id, %s)', $rootAlias, implode(',', $productIds)))
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('setHydrationMode')
                        ->once()
                        ->with(Query::HYDRATE_SIMPLEOBJECT)
                        ->andReturn($this->queryMock);

        $this->paginatorUtilsMock->shouldReceive('getPaginator')
                                 ->once()
                                 ->with($this->queryMock)
                                 ->andReturn($this->paginatorMock);

        $this->paginatorMock->shouldReceive('getQuery')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->queryMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([
                            ['root_alias_id' => 1],
                            ['root_alias_id' => 2],
                            ['root_alias_id' => 4]
                        ]);

        $this->optimizeSelectSpeedListener->onProductSearchQueryEvent($event);

        self::assertEquals($data, $event->getData());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());

        $paginationEvent = $event->getPagination();

        self::assertEquals(1, $paginationEvent->getPage());
        self::assertEquals(30, $paginationEvent->getLimit());
    }

    public function testItCanOptimizeSelectSpeedWhenItHasNotProductIds()
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination(2, 30);

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $rootAlias = 'root_alias';

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("PARTIAL {$rootAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with(30)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with(30)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('distinct')
                               ->once()
                               ->with(true)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getAllAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(['product1cb', 'BuyBox']);
        $this->queryBuilderMock->shouldReceive('getDQLPart')
                               ->once()
                               ->with('select')
                               ->andReturn([
                                   new Select('product1cb'),
                                   new Select('BuyBox'),
                                   new Select('CASE WHEN BuyBox.finalPrice < BuyBox.price THEN 1 ELSE 0 END AS HIDDEN promotionSort')
                               ]);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('CASE WHEN BuyBox.finalPrice < BuyBox.price THEN 1 ELSE 0 END AS HIDDEN promotionSort')
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id < 0")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with([])
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('orderBy')
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('setHydrationMode')
                        ->once()
                        ->with(Query::HYDRATE_SIMPLEOBJECT)
                        ->andReturn($this->queryMock);

        $this->paginatorUtilsMock->shouldReceive('getPaginator')
                                 ->once()
                                 ->with($this->queryMock)
                                 ->andReturn($this->paginatorMock);

        $this->paginatorMock->shouldReceive('getQuery')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->queryMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([]);

        $this->optimizeSelectSpeedListener->onProductSearchQueryEvent($event);

        self::assertEquals($data, $event->getData());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());

        $paginationEvent = $event->getPagination();

        self::assertEquals(1, $paginationEvent->getPage());
        self::assertEquals(30, $paginationEvent->getLimit());
    }
}
