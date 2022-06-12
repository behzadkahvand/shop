<?php

namespace App\Tests\Unit\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Product;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters\PriceRangeSearchMetaResolver;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\Doctrine\InventoryPriceRangeInterface;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PriceRangeSearchMetaResolverTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|InventoryPriceRangeInterface|null $inventoryPriceRange;

    protected QueryBuilderFilterService|LegacyMockInterface|MockInterface|null $filterServiceMock;

    protected ?SearchData $data;

    protected ?Pagination $pagination;

    protected LegacyMockInterface|MockInterface|WebsiteAreaService|null $websiteAreaMock;

    protected QueryBuilder|LegacyMockInterface|MockInterface|null $queryBuilderMock;

    protected ?PriceRangeSearchMetaResolver $priceRangeSearchMetaResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryPriceRange = Mockery::mock(InventoryPriceRangeInterface::class);
        $this->filterServiceMock   = Mockery::mock(QueryBuilderFilterService::class);
        $this->websiteAreaMock     = Mockery::mock(WebsiteAreaService::class);
        $this->queryBuilderMock    = Mockery::mock(QueryBuilder::class);

        $this->pagination = new Pagination();

        $this->priceRangeSearchMetaResolver = new PriceRangeSearchMetaResolver(
            $this->inventoryPriceRange,
            $this->filterServiceMock,
            $this->websiteAreaMock
        );
    }

    public function testItCanNotResolvePriceRangeMetaDataWhenDataTypeIsInvalid(): void
    {
        $this->data = new SearchData([], []);

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }

    public function testItCanNotResolvePriceRangeMetaDataWhenWebsiteAreaIsNotCustomer(): void
    {
        $this->data = new DoctrineSearchData([], []);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnFalse();

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }

    public function testItCanResolvePriceRangeMetaDataWithNoFilters(): void
    {
        $this->data = new DoctrineSearchData([], []);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $joinMap = [
            'App\Entity\Product'        => [
                'App\Entity\ProductVariant' => 'producte99_productVariants_3',
                'App\Entity\Category'       => 'producte99_category_6'
            ],
            'App\Entity\ProductVariant' => [
                'App\Entity\Inventory' => 'producte99_productVariants_3_inventories_4'
            ]
        ];

        $this->filterServiceMock->shouldReceive('getJoinMap')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($joinMap);
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(Product::class, [
                                    'filter' => [],
                                ])
                                ->andReturn($this->queryBuilderMock);
        $this->filterServiceMock->shouldReceive('setJoinMap')
                                ->once()
                                ->with($joinMap)
                                ->andReturnNull();

        $this->inventoryPriceRange->shouldReceive('getPriceRange')
                                  ->once()
                                  ->with($this->queryBuilderMock, [])
                                  ->andReturn([
                                      'min' => 100000,
                                      'max' => 350000
                                  ]);

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertArrayHasKey('priceRange', $result);
        self::assertCount(1, $result);
        self::assertEquals([
            'min' => 100000,
            'max' => 350000
        ], $result['priceRange']);
    }

    public function testItCanResolvePriceRangeMetaDataWithBuyBoxFinalPriceFilter(): void
    {
        $this->data = new DoctrineSearchData([
            'buyBox.finalPrice' => [
                "gte" => "100000",
                "lte" => "200000"
            ]
        ], []);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $joinMap = [
            'App\Entity\Product'        => [
                'App\Entity\ProductVariant' => 'producte99_productVariants_3',
                'App\Entity\Category'       => 'producte99_category_6'
            ],
            'App\Entity\ProductVariant' => [
                'App\Entity\Inventory' => 'producte99_productVariants_3_inventories_4'
            ]
        ];

        $this->filterServiceMock->shouldReceive('getJoinMap')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($joinMap);
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(Product::class, [
                                    'filter' => [],
                                ])
                                ->andReturn($this->queryBuilderMock);
        $this->filterServiceMock->shouldReceive('setJoinMap')
                                ->once()
                                ->with($joinMap)
                                ->andReturnNull();

        $this->inventoryPriceRange->shouldReceive('getPriceRange')
                                  ->once()
                                  ->with($this->queryBuilderMock, [])
                                  ->andReturn([
                                      'min' => 100000,
                                      'max' => 350000
                                  ]);

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertArrayHasKey('priceRange', $result);
        self::assertCount(1, $result);
        self::assertEquals([
            'min' => 100000,
            'max' => 350000
        ], $result['priceRange']);
    }

    public function testItCanResolvePriceRangeMetaDataWithBuyBoxIdFilter(): void
    {
        $this->data = new DoctrineSearchData([
            'buyBox.id' => [
                "gt" => 0
            ]
        ], []);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $joinMap = [
            'App\Entity\Product'        => [
                'App\Entity\ProductVariant' => 'producte99_productVariants_3',
                'App\Entity\Category'       => 'producte99_category_6'
            ],
            'App\Entity\ProductVariant' => [
                'App\Entity\Inventory' => 'producte99_productVariants_3_inventories_4'
            ]
        ];

        $this->filterServiceMock->shouldReceive('getJoinMap')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($joinMap);
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(Product::class, [
                                    'filter' => [],
                                ])
                                ->andReturn($this->queryBuilderMock);
        $this->filterServiceMock->shouldReceive('setJoinMap')
                                ->once()
                                ->with($joinMap)
                                ->andReturnNull();

        $this->inventoryPriceRange->shouldReceive('getPriceRange')
                                  ->once()
                                  ->with($this->queryBuilderMock, [])
                                  ->andReturn([
                                      'min' => 100000,
                                      'max' => 350000
                                  ]);

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertArrayHasKey('priceRange', $result);
        self::assertCount(1, $result);
        self::assertEquals([
            'min' => 100000,
            'max' => 350000
        ], $result['priceRange']);
    }

    public function testItCanResolvePriceRangeMetaDataWithInventoryFinalPriceFilter(): void
    {
        $this->data = new DoctrineSearchData([
            'productVariants.inventories.finalPrice' => [
                "gte" => "100000",
                "lte" => "200000"
            ]
        ], []);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $joinMap = [
            'App\Entity\Product'        => [
                'App\Entity\ProductVariant' => 'producte99_productVariants_3',
                'App\Entity\Category'       => 'producte99_category_6'
            ],
            'App\Entity\ProductVariant' => [
                'App\Entity\Inventory' => 'producte99_productVariants_3_inventories_4'
            ]
        ];

        $this->filterServiceMock->shouldReceive('getJoinMap')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($joinMap);
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(Product::class, [
                                    'filter' => [],
                                ])
                                ->andReturn($this->queryBuilderMock);
        $this->filterServiceMock->shouldReceive('setJoinMap')
                                ->once()
                                ->with($joinMap)
                                ->andReturnNull();

        $this->inventoryPriceRange->shouldReceive('getPriceRange')
                                  ->once()
                                  ->with($this->queryBuilderMock, [])
                                  ->andReturn([
                                      'min' => 100000,
                                      'max' => 350000
                                  ]);

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertArrayHasKey('priceRange', $result);
        self::assertCount(1, $result);
        self::assertEquals([
            'min' => 100000,
            'max' => 350000
        ], $result['priceRange']);
    }

    public function testItCanResolvePriceRangeMetaDataWithInventoryIdFilter(): void
    {
        $this->data = new DoctrineSearchData([
            'productVariants.inventories.id' => [
                "gt" => 0
            ]
        ], []);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $joinMap = [
            'App\Entity\Product'        => [
                'App\Entity\ProductVariant' => 'producte99_productVariants_3',
                'App\Entity\Category'       => 'producte99_category_6'
            ],
            'App\Entity\ProductVariant' => [
                'App\Entity\Inventory' => 'producte99_productVariants_3_inventories_4'
            ]
        ];

        $this->filterServiceMock->shouldReceive('getJoinMap')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($joinMap);
        $this->filterServiceMock->shouldReceive('filter')
                                ->once()
                                ->with(Product::class, [
                                    'filter' => [],
                                ])
                                ->andReturn($this->queryBuilderMock);
        $this->filterServiceMock->shouldReceive('setJoinMap')
                                ->once()
                                ->with($joinMap)
                                ->andReturnNull();

        $this->inventoryPriceRange->shouldReceive('getPriceRange')
                                  ->once()
                                  ->with($this->queryBuilderMock, [])
                                  ->andReturn([
                                      'min' => 100000,
                                      'max' => 350000
                                  ]);

        $result = $this->priceRangeSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertArrayHasKey('priceRange', $result);
        self::assertCount(1, $result);
        self::assertEquals([
            'min' => 100000,
            'max' => 350000
        ], $result['priceRange']);
    }
}
