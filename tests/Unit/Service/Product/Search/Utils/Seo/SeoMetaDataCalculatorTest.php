<?php

namespace App\Tests\Unit\Service\Product\Search\Utils\Seo;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\Seo\SeoSelectedBrandFilterRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\Seo\SeoMetaDataCalculator;
use App\Service\Product\Search\Utils\Seo\ValueObjects\SeoMetaDataValueObject;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SeoMetaDataCalculatorTest extends MockeryTestCase
{
    /**
     * @var CategoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryRepoMock;

    /**
     * @var BrandRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $brandRepoMock;

    /**
     * @var SeoSelectedBrandFilterRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $seoFilterRepoMock;

    /**
     * @var Category|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryMock;

    /**
     * @var Brand|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $brandMock;

    /**
     * @var SeoSelectedBrandFilter|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $seoSelectedFilterMock;

    protected SearchData $data;

    protected ?SeoMetaDataCalculator $seoMetaDataCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepoMock      = Mockery::mock(CategoryRepository::class);
        $this->brandRepoMock         = Mockery::mock(BrandRepository::class);
        $this->seoFilterRepoMock     = Mockery::mock(SeoSelectedBrandFilterRepository::class);
        $this->categoryMock          = Mockery::mock(Category::class);
        $this->brandMock             = Mockery::mock(Brand::class);
        $this->seoSelectedFilterMock = Mockery::mock(SeoSelectedBrandFilter::class);

        $this->seoMetaDataCalculator = new SeoMetaDataCalculator(
            $this->categoryRepoMock,
            $this->brandRepoMock,
            $this->seoFilterRepoMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->categoryRepoMock      = null;
        $this->brandRepoMock         = null;
        $this->seoFilterRepoMock     = null;
        $this->categoryMock          = null;
        $this->brandMock             = null;
        $this->seoSelectedFilterMock = null;
        $this->seoMetaDataCalculator = null;
    }

    public function testItCanGetSeoMetaDataWhenCategoryNotFound(): void
    {
        $this->data = new DoctrineSearchData(['category.code' => 'category_code'], [], 'category_code');

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturnNull();

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertNull($seoMetaData->getCategory());
        self::assertNull($seoMetaData->getTitle());
        self::assertNull($seoMetaData->getDescription());
        self::assertNull($seoMetaData->getMetaDescription());
    }

    public function testItCanGetSeoMetaDataWhenBrandFilterNotSet(): void
    {
        $this->data = new DoctrineSearchData(['category.code' => 'category_code'], [], 'category_code');

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getPageTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('title 1');
        $this->categoryMock->shouldReceive('getDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('description 1');
        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('meta description 1');

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertEquals($this->categoryMock, $seoMetaData->getCategory());
        self::assertEquals('title 1', $seoMetaData->getTitle());
        self::assertEquals('description 1', $seoMetaData->getDescription());
        self::assertEquals('meta description 1', $seoMetaData->getMetaDescription());
    }

    public function testItCanGetSeoMetaDataWhenSeveralBrandFilterSet(): void
    {
        $this->data = new DoctrineSearchData(
            [
                'category.code' => 'category_code',
                'brand.id'      => [
                    'in' => '2,3'
                ]
            ],
            [],
            'category_code'
        );

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getPageTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('title 1');
        $this->categoryMock->shouldReceive('getDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('description 1');
        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('meta description 1');

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertEquals($this->categoryMock, $seoMetaData->getCategory());
        self::assertEquals('title 1', $seoMetaData->getTitle());
        self::assertEquals('description 1', $seoMetaData->getDescription());
        self::assertEquals('meta description 1', $seoMetaData->getMetaDescription());
    }

    public function testItCanGetSeoMetaDataWhenSeoSelectedFilterNotFound(): void
    {
        $this->data = new DoctrineSearchData(
            [
                'category.code' => 'category_code',
                'brand.id'      => [
                    'in' => '2'
                ]
            ],
            [],
            'category_code'
        );

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->once()
                           ->withNoArgs()
                           ->andReturn(10);
        $this->categoryMock->shouldReceive('getPageTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('title 1');
        $this->categoryMock->shouldReceive('getDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('description 1');
        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('meta description 1');

        $this->seoFilterRepoMock->shouldReceive('findOneByCategoryAndBrand')
                                ->once()
                                ->with(10, 2)
                                ->andReturnNull();

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertEquals($this->categoryMock, $seoMetaData->getCategory());
        self::assertEquals('title 1', $seoMetaData->getTitle());
        self::assertEquals('description 1', $seoMetaData->getDescription());
        self::assertEquals('meta description 1', $seoMetaData->getMetaDescription());
    }

    public function testItCanGetSeoMetaDataWhenBrandNotFoundAndSeoSelectedFilterHasNoData(): void
    {
        $this->data = new DoctrineSearchData(
            [
                'category.code' => 'category_code',
                'brand.id'      => [
                    'in' => '2'
                ]
            ],
            [],
            'category_code'
        );

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->once()
                           ->withNoArgs()
                           ->andReturn(10);
        $this->categoryMock->shouldReceive('getPageTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('title 1');
        $this->categoryMock->shouldReceive('getDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('description 1');
        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('meta description 1');

        $this->seoFilterRepoMock->shouldReceive('findOneByCategoryAndBrand')
                                ->once()
                                ->with(10, 2)
                                ->andReturn($this->seoSelectedFilterMock);

        $this->brandRepoMock->shouldReceive('findOneBy')
                            ->once()
                            ->with(['id' => 2])
                            ->andReturnNull();

        $this->seoSelectedFilterMock->shouldReceive('getTitle')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturnNull();
        $this->seoSelectedFilterMock->shouldReceive('getDescription')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturnNull();
        $this->seoSelectedFilterMock->shouldReceive('getMetaDescription')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturnNull();

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertEquals($this->categoryMock, $seoMetaData->getCategory());
        self::assertEquals('title 1', $seoMetaData->getTitle());
        self::assertEquals('description 1', $seoMetaData->getDescription());
        self::assertEquals('meta description 1', $seoMetaData->getMetaDescription());
    }

    public function testItCanGetSeoMetaDataWhenSeoSelectedFilterHasNoData(): void
    {
        $this->data = new DoctrineSearchData(
            [
                'category.code' => 'category_code',
                'brand.id'      => [
                    'in' => '2'
                ]
            ],
            [],
            'category_code'
        );

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->once()
                           ->withNoArgs()
                           ->andReturn(10);
        $this->categoryMock->shouldReceive('getPageTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('title 1');
        $this->categoryMock->shouldReceive('getDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('description 1');
        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('meta description 1');
        $this->categoryMock->shouldReceive('getTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('Mobile');

        $this->seoFilterRepoMock->shouldReceive('findOneByCategoryAndBrand')
                                ->once()
                                ->with(10, 2)
                                ->andReturn($this->seoSelectedFilterMock);

        $this->brandRepoMock->shouldReceive('findOneBy')
                            ->once()
                            ->with(['id' => 2])
                            ->andReturn($this->brandMock);

        $this->brandMock->shouldReceive('getTitle')
                        ->once()
                        ->withNoArgs()
                        ->andReturn('Samsung');

        $this->seoSelectedFilterMock->shouldReceive('getTitle')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturnNull();
        $this->seoSelectedFilterMock->shouldReceive('getDescription')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturnNull();
        $this->seoSelectedFilterMock->shouldReceive('getMetaDescription')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturnNull();

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertEquals($this->categoryMock, $seoMetaData->getCategory());
        self::assertEquals('خرید و قیمت Mobile Samsung | تیمچه', $seoMetaData->getTitle());
        self::assertEquals('description 1', $seoMetaData->getDescription());
        self::assertEquals('meta description 1', $seoMetaData->getMetaDescription());
    }

    public function testItCanGetSeoMetaDataWhenSeoSelectedFilterHasData(): void
    {
        $this->data = new DoctrineSearchData(
            [
                'category.code' => 'category_code',
                'brand.id'      => [
                    'in' => '2'
                ]
            ],
            [],
            'category_code'
        );

        $this->categoryRepoMock->shouldReceive('findOneBy')
                               ->once()
                               ->with(['code' => 'category_code'])
                               ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->once()
                           ->withNoArgs()
                           ->andReturn(10);
        $this->categoryMock->shouldReceive('getPageTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('title 1');
        $this->categoryMock->shouldReceive('getDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('description 1');
        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('meta description 1');
        $this->categoryMock->shouldReceive('getTitle')
                           ->once()
                           ->withNoArgs()
                           ->andReturn('Mobile');

        $this->seoFilterRepoMock->shouldReceive('findOneByCategoryAndBrand')
                                ->once()
                                ->with(10, 2)
                                ->andReturn($this->seoSelectedFilterMock);

        $this->brandRepoMock->shouldReceive('findOneBy')
                            ->once()
                            ->with(['id' => 2])
                            ->andReturn($this->brandMock);

        $this->brandMock->shouldReceive('getTitle')
                        ->once()
                        ->withNoArgs()
                        ->andReturn('Samsung');

        $this->seoSelectedFilterMock->shouldReceive('getTitle')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturn('Seo filter title');
        $this->seoSelectedFilterMock->shouldReceive('getDescription')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturn('Seo filter description');
        $this->seoSelectedFilterMock->shouldReceive('getMetaDescription')
                                    ->once()
                                    ->withNoArgs()
                                    ->andReturn('Seo filter meta description');

        $seoMetaData = $this->seoMetaDataCalculator->getData($this->data);

        self::assertEquals($this->categoryMock, $seoMetaData->getCategory());
        self::assertEquals('Seo filter title', $seoMetaData->getTitle());
        self::assertEquals('Seo filter description', $seoMetaData->getDescription());
        self::assertEquals('Seo filter meta description', $seoMetaData->getMetaDescription());
    }
}
