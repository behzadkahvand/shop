<?php

namespace App\Tests\Unit\Service\Product\Search\Meta\Adapters\Doctrine;

use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Adapters\Doctrine\DoctrineSearchMetaResolverAdapter;
use App\Service\Product\Search\Meta\SearchMetaResolverInterface;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DoctrineSearchMetaResolverAdapterTest extends MockeryTestCase
{
    /**
     * @var SearchMetaResolverInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $resolverMock;

    protected SearchData $data;

    protected Pagination $pagination;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    protected DoctrineSearchMetaResolverAdapter $searchMetaResolverAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolverMock     = Mockery::mock(SearchMetaResolverInterface::class);
        $this->queryBuilderMock = Mockery::mock(QueryBuilder::class);

        $this->pagination = new Pagination();

        $this->searchMetaResolverAdapter = new DoctrineSearchMetaResolverAdapter([
            $this->resolverMock,
            $this->resolverMock,
            $this->resolverMock
        ]);
    }

    protected function tearDown(): void
    {
        unset($this->pagination, $this->searchMetaResolverAdapter);

        $this->resolverMock = null;
        $this->queryBuilderMock = null;
    }

    public function testItCanResolveSearchMetaDataWhenSearchDataTypeIsDoctrine()
    {
        $this->data = new DoctrineSearchData([], []);

        $this->resolverMock->shouldReceive('resolve')
                           ->times(3)
                           ->with($this->queryBuilderMock, $this->data, $this->pagination)
                           ->andReturn([]);

        $result = $this->searchMetaResolverAdapter->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }

    public function testItCanResolveSearchMetaDataWhenSearchDataTypeIsNotDoctrine()
    {
        $this->data = new SearchData([], []);

        $result = $this->searchMetaResolverAdapter->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }
}
