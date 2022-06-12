<?php

namespace App\Tests\Unit\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters\PaginationSearchMetaResolver;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\Pagination\PaginatorUtils;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PaginationSearchMetaResolverTest extends MockeryTestCase
{
    /**
     * @var PaginatorUtils|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $paginatorUtilsMock;

    protected SearchData $data;

    protected Pagination $pagination;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    protected PaginationSearchMetaResolver $paginationSearchMetaResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paginatorUtilsMock = Mockery::mock(PaginatorUtils::class);
        $this->queryBuilderMock   = Mockery::mock(QueryBuilder::class);

        $this->pagination = new Pagination();

        $this->paginationSearchMetaResolver = new PaginationSearchMetaResolver($this->paginatorUtilsMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->pagination,
            $this->paginationSearchMetaResolver
        );

        $this->paginatorUtilsMock = null;
        $this->queryBuilderMock   = null;
    }

    public function testItCanNotResolvePaginationMetaData()
    {
        $this->data = new SearchData([], []);

        $result = $this->paginationSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }

    public function testItCanResolvePaginationMetaData()
    {
        $this->data = new DoctrineSearchData([], []);

        $this->paginatorUtilsMock->shouldReceive('getCount')
                                 ->once()
                                 ->with($this->queryBuilderMock)
                                 ->andReturn(10);

        $result = $this->paginationSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(4, $result);
        self::assertArrayHasKey('page', $result);
        self::assertArrayHasKey('perPage', $result);
        self::assertArrayHasKey('totalItems', $result);
        self::assertArrayHasKey('totalPages', $result);

        self::assertEquals(1, $result['page']);
        self::assertEquals(20, $result['perPage']);
        self::assertEquals(10, $result['totalItems']);
        self::assertEquals(1, $result['totalPages']);
    }
}
