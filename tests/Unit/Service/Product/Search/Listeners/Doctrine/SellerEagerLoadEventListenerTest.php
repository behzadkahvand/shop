<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Category;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\SellerEagerLoadEventListener;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\Queries\Doctrine\QuerySearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class SellerEagerLoadEventListenerTest extends BaseUnitTestCase
{
    protected QueryBuilderFilterService|LegacyMockInterface|MockInterface|null $filterServiceMock;

    protected QueryBuilder|LegacyMockInterface|MockInterface|null $queryBuilderMock;

    protected AbstractQuery|LegacyMockInterface|MockInterface|null $queryMock;

    protected LegacyMockInterface|MockInterface|WebsiteAreaService|null $websiteAreaMock;

    protected ?SellerEagerLoadEventListener $eagerLoadEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);
        $this->websiteAreaMock   = Mockery::mock(WebsiteAreaService::class);

        $this->eagerLoadEventListener = new SellerEagerLoadEventListener($this->filterServiceMock, $this->websiteAreaMock);
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->eagerLoadEventListener::getSubscribedEvents();

        self::assertEquals([ProductSearchQueryEvent::class => 'onProductSearchQueryEvent'], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            'invalid',
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        self::assertNull($this->eagerLoadEventListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotSeller(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnFalse();

        self::assertNull($this->eagerLoadEventListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());
    }

    public function testItCanUpdateSearchQueryWithCategoryAndVariantJoinAndWithoutBuyBoxJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL image.{id, path, alt}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL seller.{id, identifier, name}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.category", "category")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL category.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.productVariants", "ProductVariants")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL ProductVariants.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with('ProductVariants.inventories', 'inventories')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                               ->andReturn($this->queryBuilderMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithCategoryAndVariantJoinAndWithBuyBoxJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", "BuyBox")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.category", "category")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL category.{id}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.productVariants", "ProductVariants")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL ProductVariants.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with('ProductVariants.inventories', 'inventories')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithoutCategoryAndVariantJoinAndWithoutBuyBoxJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias     = 'root_alias';
        $categoryAlias = 'category_alias';
        $buyBoxAlias   = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$categoryAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturn($categoryAlias);


        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.productVariants", "ProductVariants")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL ProductVariants.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with('ProductVariants.inventories', 'inventories')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithoutCategoryAndVariantJoinAndWithBuyBoxJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias     = 'root_alias';
        $categoryAlias = 'category_alias';
        $buyBoxAlias   = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", "BuyBox")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$categoryAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturn($categoryAlias);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.productVariants", "ProductVariants")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL ProductVariants.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with('ProductVariants.inventories', 'inventories')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithCategoryJoinAndWithoutBuyBoxAndVariantJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL image.{id, path, alt}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL seller.{id, identifier, name}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.category", "category")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL category.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $variantAlias = 'variant_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturn($variantAlias);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithCategoryJoinAndWithBuyBoxAndVariantJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", "BuyBox")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.category", "category")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL category.{id}")
                               ->andReturn($this->queryBuilderMock);

        $variantAlias = 'variant_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturn($variantAlias);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithoutCategoryJoinAndWithoutBuyBoxAndVariantJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias     = 'root_alias';
        $categoryAlias = 'category_alias';
        $buyBoxAlias   = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$categoryAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturn($categoryAlias);

        $variantAlias = 'variant_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturn($variantAlias);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }

    public function testItCanUpdateSearchQueryWithoutCategoryJoinAndWithBuyBoxAndVariantJoin(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $rootAlias     = 'root_alias';
        $categoryAlias = 'category_alias';
        $buyBoxAlias   = 'BuyBox';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", "BuyBox")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$categoryAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturn($categoryAlias);

        $variantAlias = 'variant_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturn($variantAlias);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }
}
