<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Category;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Events\Product\Search\SellerProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\Seller\CustomerEagerLoadEventListener;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\Queries\Doctrine\QuerySearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class CustomerEagerLoadEventListenerTest extends BaseUnitTestCase
{
    protected QueryBuilderFilterService|LegacyMockInterface|MockInterface|null $filterServiceMock;

    protected QueryBuilder|LegacyMockInterface|MockInterface|null $queryBuilderMock;

    protected AbstractQuery|LegacyMockInterface|MockInterface|null $queryMock;

    protected LegacyMockInterface|MockInterface|WebsiteAreaService|null $websiteAreaMock;

    protected ?CustomerEagerLoadEventListener $eagerLoadEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);
        $this->websiteAreaMock   = Mockery::mock(WebsiteAreaService::class);

        $this->eagerLoadEventListener = new CustomerEagerLoadEventListener($this->filterServiceMock, $this->websiteAreaMock);
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->eagerLoadEventListener::getSubscribedEvents();

        self::assertEquals([SellerProductSearchQueryEvent::class => 'onProductSearchQueryEvent'], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new SellerProductSearchQueryEvent(
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

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new SellerProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnFalse();

        self::assertNull($this->eagerLoadEventListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QueryBuilderSearchQuery::class, $event->getQuery());
    }

    public function testItCanUpdateSearchQuery(): void
    {
        $data       = new SearchData([], []);
        $pagination = new Pagination();

        $event = new SellerProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $rootAlias      = 'root_alias';
        $buyBoxAlias    = 'buy_box_alias';
        $variantAlias   = 'variant_alias';
        $inventoryAlias = 'inventory_alias';
        $sellerAlias    = 'seller_alias';

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("partial {$rootAlias}.{id, title, subtitle, alternativeTitle, colors, status}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.featuredImage", "image")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL image.{id, path, alt}')
                               ->andReturn($this->queryBuilderMock);

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, ProductVariant::class)
                                ->andReturn($variantAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(ProductVariant::class, Inventory::class)
                                ->andReturn($inventoryAlias);
        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Category::class)
                                ->andReturnNull();

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Inventory::class, Seller::class)
                                ->andReturn($sellerAlias);

        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$rootAlias}.category", "category")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL category.{id, commission}")
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$variantAlias}.{id}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$inventoryAlias}.{id, price, finalPrice, leadTime, isActive, status, sellerStock}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$variantAlias}.optionValues", "optionValues")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL optionValues.{id, value, code, attributes}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("optionValues.option", "productOption")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL productOption.{id, code, name}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$sellerAlias}.{id, identifier, name}")
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);

        $this->queryMock->shouldReceive('setHint')
                        ->once()
                        ->with(Query::HINT_FORCE_PARTIAL_LOAD, true)
                        ->andReturn($this->queryMock);

        $this->eagerLoadEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals($data, $event->getData());
        self::assertEquals($pagination, $event->getPagination());
        self::assertInstanceOf(QuerySearchQuery::class, $event->getQuery());

        self::assertTrue($event->isPropagationStopped());
    }
}
