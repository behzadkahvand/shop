<?php

namespace App\Tests\Unit\Service\Product\Search;

use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Events\Product\Search\ProductSearchResultEvent;
use App\Service\Product\Search\ProductSearchDriverInterface;
use App\Service\Product\Search\ProductSearchService;
use App\Service\Product\Search\Queries\AbstractSearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\SearchResult;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\Pagination\PaginatorInterface;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSearchServiceTest extends MockeryTestCase
{
    /**
     * @var ProductSearchDriverInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $searchDriverMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    protected $dispatcherMock;

    /**
     * @var PaginatorInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $paginatorMock;

    /**
     * @var AbstractSearchQuery|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $searchQueryMock;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    protected SearchData $searchData;

    protected Pagination $pagination;

    protected ProductSearchService $productSearchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchDriverMock = Mockery::mock(ProductSearchDriverInterface::class);
        $this->dispatcherMock   = Mockery::mock(EventDispatcherInterface::class);
        $this->paginatorMock    = Mockery::mock(PaginatorInterface::class);
        $this->searchQueryMock  = Mockery::mock(AbstractSearchQuery::class);
        $this->queryBuilderMock = Mockery::mock(QueryBuilder::class);

        $this->searchData = new SearchData([], []);
        $this->pagination = new Pagination();

        $this->productSearchService = new ProductSearchService(
            $this->searchDriverMock,
            $this->dispatcherMock,
            $this->paginatorMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->searchData,
            $this->pagination,
            $this->productSearchService
        );

        $this->searchDriverMock = null;
        $this->dispatcherMock = null;
        $this->paginatorMock = null;
        $this->searchQueryMock = null;
        $this->queryBuilderMock = null;
    }

    public function testItCanSearchProduct()
    {
        $this->dispatcherMock->shouldReceive('dispatch')
                             ->with(Mockery::type(ProductSearchDataEvent::class))
                             ->once();

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->with(Mockery::type(ProductSearchQueryEvent::class))
                             ->once();

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->with(Mockery::type(ProductSearchResultEvent::class))
                             ->once();

        $this->searchDriverMock->shouldReceive('getQuery')
                               ->once()
                               ->with($this->searchData, $this->pagination)
                               ->andReturn($this->searchQueryMock);

        $this->searchQueryMock->shouldReceive('getMeta')
                              ->once()
                              ->withNoArgs()
                              ->andReturn([]);

        $this->searchQueryMock->shouldReceive('getResultQuery')
                              ->once()
                              ->withNoArgs()
                              ->andReturn($this->queryBuilderMock);

        $this->paginatorMock->shouldReceive('paginate')
                            ->once()
                            ->with($this->queryBuilderMock, $this->pagination)
                            ->andReturn([]);

        $result = $this->productSearchService->search($this->searchData, $this->pagination);

        self::assertInstanceOf(SearchResult::class, $result);
    }
}
