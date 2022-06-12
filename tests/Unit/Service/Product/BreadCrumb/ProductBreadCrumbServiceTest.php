<?php

namespace App\Tests\Unit\Service\Product\BreadCrumb;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Service\Product\BreadCrumb\ProductBreadCrumbService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ProductBreadCrumbServiceTest extends MockeryTestCase
{
    /**
     * @var CategoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryRepoMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|CacheItemPoolInterface
     */
    protected $cacheMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|CacheItemInterface
     */
    protected $cacheItemMock;

    /**
     * @var Product|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $productMock;

    /**
     * @var Category|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryMock;

    protected ProductBreadCrumbService $productBreadCrumbService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepoMock = Mockery::mock(CategoryRepository::class);
        $this->cacheMock        = Mockery::mock(CacheItemPoolInterface::class);
        $this->cacheItemMock    = Mockery::mock(CacheItemInterface::class);
        $this->productMock      = Mockery::mock(Product::class);
        $this->categoryMock     = Mockery::mock(Category::class);

        $this->productBreadCrumbService = new ProductBreadCrumbService(
            $this->categoryRepoMock,
            $this->cacheMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->productBreadCrumbService);

        $this->categoryRepoMock = null;
        $this->cacheMock        = null;
        $this->productMock      = null;
        $this->categoryMock     = null;
    }

    public function testItCanGetProductBreadCrumbFromCache(): void
    {
        $this->productMock->shouldReceive('getCategory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->once()
                           ->withNoArgs()
                           ->andReturn(152);

        $this->cacheMock->shouldReceive('getItem')
                        ->once()
                        ->with('category_path_152')
                        ->andReturn($this->cacheItemMock);

        $breadcrumbData = [
            [
                "id"        => 1,
                "code"      => "digital",
                "title"     => "دیجیتال",
                "image"     => null,
                "pageTitle" => "دیجیتال | تیمچه",
                "subtitle"  => "digital"
            ],
            [
                "id"        => 2,
                "code"      => "mobile",
                "title"     => "موبایل",
                "image"     => null,
                "pageTitle" => "خرید موبایل - لیست قیمت جدیدترین موبایل های بازار",
                "subtitle"  => "mobile"
            ],
            [
                "id"        => 36,
                "code"      => "mobile-phone",
                "title"     => "گوشی موبایل",
                "image"     => null,
                "pageTitle" => "خرید گوشی موبایل - لیست قیمت جدیدترین گوشی های بازار",
                "subtitle"  => "Mobile Phone"
            ]
        ];

        $this->cacheItemMock->shouldReceive('get')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($breadcrumbData);

        $result = $this->productBreadCrumbService->get($this->productMock);

        self::assertEquals($breadcrumbData, $result);
    }

    public function testItCanGetProductBreadCrumbFromDatabase(): void
    {
        $this->productMock->shouldReceive('getCategory')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->categoryMock);

        $this->categoryMock->shouldReceive('getId')
                           ->once()
                           ->withNoArgs()
                           ->andReturn(152);

        $this->cacheMock->shouldReceive('getItem')
                        ->once()
                        ->with('category_path_152')
                        ->andReturn($this->cacheItemMock);

        $this->cacheItemMock->shouldReceive('get')
                            ->once()
                            ->withNoArgs()
                            ->andReturnNull();

        $this->categoryRepoMock->shouldReceive('getPath')
                               ->once()
                               ->with($this->categoryMock)
                               ->andReturn([
                                   $this->categoryMock,
                                   $this->categoryMock,
                                   $this->categoryMock
                               ]);

        $this->categoryMock->shouldReceive('getId')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn(1, 2, 36);
        $this->categoryMock->shouldReceive('getCode')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn("digital", "mobile", "mobile-phone");
        $this->categoryMock->shouldReceive('getTitle')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn("دیجیتال", "موبایل", "گوشی موبایل");
        $this->categoryMock->shouldReceive('getImage')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturnNull();
        $this->categoryMock->shouldReceive('getPageTitle')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn("دیجیتال | تیمچه", "خرید موبایل - لیست قیمت جدیدترین موبایل های بازار", "خرید گوشی موبایل - لیست قیمت جدیدترین گوشی های بازار");
        $this->categoryMock->shouldReceive('getSubtitle')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn("digital", "mobile", "Mobile Phone");

        $breadcrumbData = [
            [
                "id"        => 1,
                "code"      => "digital",
                "title"     => "دیجیتال",
                "image"     => null,
                "pageTitle" => "دیجیتال | تیمچه",
                "subtitle"  => "digital"
            ],
            [
                "id"        => 2,
                "code"      => "mobile",
                "title"     => "موبایل",
                "image"     => null,
                "pageTitle" => "خرید موبایل - لیست قیمت جدیدترین موبایل های بازار",
                "subtitle"  => "mobile"
            ],
            [
                "id"        => 36,
                "code"      => "mobile-phone",
                "title"     => "گوشی موبایل",
                "image"     => null,
                "pageTitle" => "خرید گوشی موبایل - لیست قیمت جدیدترین گوشی های بازار",
                "subtitle"  => "Mobile Phone"
            ]
        ];

        $this->cacheItemMock->shouldReceive('set')
                            ->once()
                            ->with($breadcrumbData)
                            ->andReturn($this->cacheItemMock);
        $this->cacheItemMock->shouldReceive('expiresAfter')
                            ->once()
                            ->with(86400)
                            ->andReturn($this->cacheItemMock);

        $this->cacheMock->shouldReceive('save')
                        ->once()
                        ->with($this->cacheItemMock)
                        ->andReturnTrue();

        $result = $this->productBreadCrumbService->get($this->productMock);

        self::assertEquals($breadcrumbData, $result);
    }
}
