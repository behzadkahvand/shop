<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Entity\Category;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\CustomerEagerLoadEventListener;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\Queries\Doctrine\QuerySearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CustomerEagerLoadEventListenerTest extends MockeryTestCase
{
    /**
     * @var QueryBuilderFilterService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $filterServiceMock;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    /**
     * @var AbstractQuery|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryMock;

    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    protected CustomerEagerLoadEventListener $eagerLoadEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);
        $this->websiteAreaMock   = Mockery::mock(WebsiteAreaService::class);

        $this->eagerLoadEventListener = new CustomerEagerLoadEventListener($this->filterServiceMock, $this->websiteAreaMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->filterServiceMock,
            $this->queryBuilderMock,
            $this->queryMock,
            $this->websiteAreaMock,
            $this->eagerLoadEventListener
        );
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

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer(): void
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

        $event = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $data,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $rootAlias     = 'root_alias';
        $buyBoxAlias   = 'buy_box_alias';

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
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime, hasCampaign}")
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
                                ->with(Product::class, Category::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('innerJoin')
                               ->once()
                               ->with("{$rootAlias}.category", 'category')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL category.{id, commission}")
                               ->andReturn($this->queryBuilderMock);

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.productVariants", 'ProductVariants')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL ProductVariants.{id}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with('ProductVariants.inventories', 'Inventories')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("ProductVariants.optionValues", "optionValues")
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
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$buyBoxAlias}.seller", "seller")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL seller.{id, identifier, name}')
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
