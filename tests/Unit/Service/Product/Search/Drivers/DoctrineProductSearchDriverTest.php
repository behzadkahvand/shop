<?php

namespace App\Tests\Unit\Service\Product\Search\Drivers;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Product;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\UnsupportedSearchDataTypeException;
use App\Service\Product\Search\Meta\SearchMetaResolverInterface;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DoctrineProductSearchDriverTest extends MockeryTestCase
{
    /**
     * @var QueryBuilderFilterService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $filterServiceMock;

    /**
     * @var SearchMetaResolverInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $metaResolverMock;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    /**
     * @var AbstractQuery|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryMock;

    protected DoctrineSearchData $data;

    protected Pagination $pagination;

    protected array $statuses;

    protected DoctrineProductSearchDriver $doctrineProductSearchDriver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->metaResolverMock  = Mockery::mock(SearchMetaResolverInterface::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);

        $this->pagination = new Pagination();

        $this->statuses = [
            ProductStatusDictionary::CONFIRMED,
            ProductStatusDictionary::UNAVAILABLE,
            ProductStatusDictionary::SOON,
            ProductStatusDictionary::SHUTDOWN,
        ];

        $this->doctrineProductSearchDriver = new DoctrineProductSearchDriver(
            $this->filterServiceMock,
            $this->metaResolverMock
        );
    }



    protected function tearDown(): void
    {
        unset($this->doctrineProductSearchDriver, $this->pagination, $this->statuses);

        $this->filterServiceMock = null;
        $this->metaResolverMock = null;
        $this->queryBuilderMock = null;
        $this->queryMock = null;
    }

    public function testItThrowsInvalidArgumentExceptionOnUnexpectedDataType()
    {
        $this->expectException(UnsupportedSearchDataTypeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage(sprintf('Expected instance of %s got %s', DoctrineSearchData::class, SearchData::class));

        $this->doctrineProductSearchDriver->getQuery(new SearchData([], []), $this->pagination);
    }

    public function testItCanSearchProductWithDoctrineDriver()
    {
        $this->data = new DoctrineSearchData(['category.code' => 'category_code'], [], 'category_code');

        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(
                                    Product::class,
                                    [
                                        'filter' => $this->data->getFilters(),
                                        'sort'   => $this->data->getSorts(),
                                    ]
                                )
                                ->andReturn($this->queryBuilderMock);

        $this->metaResolverMock->shouldReceive('resolve')
                               ->once()
                               ->with($this->queryBuilderMock, $this->data, $this->pagination)
                               ->andReturn(
                                   [
                                       'page'       => $this->pagination->getPage(),
                                       'perPage'    => $this->pagination->getLimit(),
                                       'totalItems' => 12,
                                       'totalPages' => (int)ceil(12 / $this->pagination->getLimit()),
                                       'priceRange' => [
                                           'min' => 100000,
                                           'max' => 350000
                                       ],
                                       'brands'     => [
                                           'title' => null,
                                           'items' => [],
                                       ],
                                       'categories' => [
                                           'breadcrumb' => [],
                                           'hierarchy'  => []
                                       ],
                                   ]
                               );

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->queryMock->shouldReceive('getResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([]);

        $result = $this->doctrineProductSearchDriver->getQuery($this->data, $this->pagination);

        self::assertInstanceOf(QueryBuilderSearchQuery::class, $result);
        self::assertEquals($this->queryBuilderMock, $result->getDoctrineQueryBuilder());
        self::assertEquals([], $result->getResult());
        self::assertCount(7, $result->getMeta());
    }
}
