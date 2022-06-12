<?php

namespace App\Tests\Unit\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters\BrandSearchMetaResolver;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BrandSearchMetaResolverTest extends MockeryTestCase
{
    /**
     * @var BrandRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $brandRepoMock;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    /**
     * @var Brand|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $brandMock;

    protected SearchData $data;

    protected Pagination $pagination;

    protected BrandSearchMetaResolver $brandSearchMetaResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandRepoMock    = Mockery::mock(BrandRepository::class);
        $this->queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $this->brandMock        = Mockery::mock(Brand::class);

        $this->pagination = new Pagination();

        $this->brandSearchMetaResolver = new BrandSearchMetaResolver($this->brandRepoMock);
    }

    protected function tearDown(): void
    {
        unset($this->pagination, $this->brandSearchMetaResolver);

        $this->brandMock        = null;
        $this->queryBuilderMock = null;
    }

    public function testItCanNotResolveBrandSearchMetaData(): void
    {
        $this->data = new SearchData([], []);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }

    public function testItCanResolveBrandSearchMetaDataWhenBrandCodeFilterIsNotSet(): void
    {
        $this->data = new DoctrineSearchData([], []);

        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with([], null)
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenCategoryFilterSets(): void
    {
        $this->data = new DoctrineSearchData([
            'category.id' => [
                'in' => '7,8'
            ]
        ], []);

        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with(['7', '8'], null)
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenCategoryAndTitleFilterSets(): void
    {
        $this->data = new DoctrineSearchData([
            'category.id' => [
                'in' => '7,8'
            ]
        ], [], 'category_code', 'title');

        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with(['7', '8'], 'title')
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenExcludedCategoriesSetAndExist(): void
    {
        $this->data = new DoctrineSearchData([
            'category.id' => [
                'in'  => '7,8',
                'nin' => '7'
            ]
        ], [], 'category_code', 'title');

        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with(['8'], 'title')
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenExcludedCategoriesSetAndNotExist(): void
    {
        $this->data = new DoctrineSearchData([
            'category.id' => [
                'in'  => '7,8',
                'nin' => '10'
            ]
        ], [], 'category_code', 'title');

        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with(['7', '8'], 'title')
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenTitleFilterSets(): void
    {
        $this->data = new DoctrineSearchData([], [], 'category_code', 'title');

        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with([], 'title')
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenBrandExist(): void
    {
        $this->data = new DoctrineSearchData(['brand.code' => 'brand_code'], []);

        $this->brandRepoMock->shouldReceive('findOneBy')
                            ->once()
                            ->with(['code' => 'brand_code'])
                            ->andReturn($this->brandMock);
        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with([], null)
                            ->andReturn([]);

        $this->brandMock->shouldReceive('getTitle')
                        ->once()
                        ->withNoArgs()
                        ->andReturn('brand_name');

        $this->brandMock->shouldReceive('getDescription')
                        ->once()
                        ->withNoArgs()
                        ->andReturn('description of brand');

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals('brand_name', $brands['title']);
        self::assertEquals('description of brand', $brands['description']);
        self::assertEquals([], $brands['items']);
    }

    public function testItCanResolveBrandSearchMetaDataWhenBrandDoesNotExist(): void
    {
        $this->data = new DoctrineSearchData(['brand.code' => 'brand_code'], []);

        $this->brandRepoMock->shouldReceive('findOneBy')
                            ->once()
                            ->with(['code' => 'brand_code'])
                            ->andReturnNull();
        $this->brandRepoMock->shouldReceive('getBrandsForProductSearch')
                            ->once()
                            ->with([], null)
                            ->andReturn([]);

        $result = $this->brandSearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('brands', $result);
        $brands = $result['brands'];
        self::assertCount(3, $brands);
        self::assertArrayHasKey('title', $brands);
        self::assertArrayHasKey('description', $brands);
        self::assertArrayHasKey('items', $brands);
        self::assertEquals(null, $brands['title']);
        self::assertEquals([], $brands['items']);
    }
}
