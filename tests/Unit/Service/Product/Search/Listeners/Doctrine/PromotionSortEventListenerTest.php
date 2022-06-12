<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\PromotionSortEventListener;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class PromotionSortEventListenerTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var QueryBuilderFilterService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $filterServiceMock;

    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    /**
     * @var AbstractQuery|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryMock;

    protected PromotionSortEventListener $promotionSortEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->websiteAreaMock   = Mockery::mock(WebsiteAreaService::class);
        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class, [$this->em]);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);

        $this->promotionSortEventListener = new PromotionSortEventListener(
            $this->websiteAreaMock,
            $this->filterServiceMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->promotionSortEventListener,
            $this->filterServiceMock,
            $this->websiteAreaMock,
            $this->em,
            $this->queryBuilderMock,
            $this->queryMock,
        );
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->promotionSortEventListener::getSubscribedEvents();

        self::assertEquals([
            ProductSearchDataEvent::class  => 'onProductSearchDataEvent',
            ProductSearchQueryEvent::class => ['onProductSearchQueryEvent', 2],
        ], $result);
    }

    public function testItDoNothingWhenDriverIsInvalidForSearchDataEvent(): void
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->promotionSortEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomerForSearchDataEvent(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnFalse();

        self::assertNull($this->promotionSortEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingPromotionSortNotSetForSearchDataEvent(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], ['-visits']),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        self::assertNull($this->promotionSortEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
    }

    public function testItCanSetApplyPromotionSortInSearchDataEventAndPromotionProductsIsInTop(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->promotionSortEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "promotion",
            "direction"        => "ASC",
            "direction_prefix" => ""
        ], (fn() => $this->sortData)->call($this->promotionSortEventListener));
    }

    public function testItCanSetApplyPromotionSortInSearchDataEventAndPromotionProductsIsInDown(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->promotionSortEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "promotion",
            "direction"        => "DESC",
            "direction_prefix" => "-"
        ], (fn() => $this->sortData)->call($this->promotionSortEventListener));
    }

    public function testItDoNothingPromotionSortNotSetForSearchQueryEvent(): void
    {
        $searchData = new SearchData([], ['-visits']);
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $searchData,
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            $searchData,
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->promotionSortEventListener->onProductSearchDataEvent($event);

        self::assertNull($this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent));

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertNull((fn() => $this->sortData ?? null)->call($this->promotionSortEventListener));
    }

    public function testItDoNothingWhenDriverIsInvalidForSearchQueryEvent(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            'invalid',
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            new DoctrineSearchData(
                [],
                ['-visits'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->promotionSortEventListener->onProductSearchDataEvent($event);

        self::assertNull($this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent));

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "promotion",
            "direction"        => "ASC",
            "direction_prefix" => ""
        ], (fn() => $this->sortData)->call($this->promotionSortEventListener));
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomerForSearchQueryEvent(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            new DoctrineSearchData(
                [],
                ['-visits'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->twice()
                              ->withNoArgs()
                              ->andReturn(true, false);

        $this->promotionSortEventListener->onProductSearchDataEvent($event);

        self::assertNull($this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent));

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "promotion",
            "direction"        => "DESC",
            "direction_prefix" => "-"
        ], (fn() => $this->sortData)->call($this->promotionSortEventListener));
    }

    public function testItCanAddPromotionSortAndPromotionProductsIsInTopWhenInventoryJoinExists(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            new DoctrineSearchData(
                [],
                ['-visits'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->twice()
                              ->withNoArgs()
                              ->andReturnTrue();

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'buy_box_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with(sprintf(
                                   'CASE WHEN %1$s.finalPrice < %1$s.price THEN 1 ELSE 0 END AS HIDDEN promotionSort',
                                   $buyBoxAlias
                               ))
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('promotionSort', 'DESC')
                               ->andReturn($this->queryBuilderMock);

        $this->promotionSortEventListener->onProductSearchDataEvent($event);
        $this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->sortData ?? null)->call($this->promotionSortEventListener));
    }

    public function testItCanAddPromotionSortAndPromotionProductsIsInTopWhenInventoryJoinNotExists(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            new DoctrineSearchData(
                [],
                ['-visits'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->twice()
                              ->withNoArgs()
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
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", $buyBoxAlias)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with(sprintf(
                                   'CASE WHEN %1$s.finalPrice < %1$s.price THEN 1 ELSE 0 END AS HIDDEN promotionSort',
                                   $buyBoxAlias
                               ))
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('promotionSort', 'DESC')
                               ->andReturn($this->queryBuilderMock);

        $this->promotionSortEventListener->onProductSearchDataEvent($event);
        $this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->sortData ?? null)->call($this->promotionSortEventListener));
    }

    public function testItCanAddPromotionSortAndPromotionProductsIsInDownWhenInventoryJoinExists(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            new DoctrineSearchData(
                [],
                ['-visits'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->twice()
                              ->withNoArgs()
                              ->andReturnTrue();

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'buy_box_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with(sprintf(
                                   'CASE WHEN %1$s.finalPrice < %1$s.price THEN 1 ELSE 0 END AS HIDDEN promotionSort',
                                   $buyBoxAlias
                               ))
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('promotionSort', 'ASC')
                               ->andReturn($this->queryBuilderMock);

        $this->promotionSortEventListener->onProductSearchDataEvent($event);
        $this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->sortData ?? null)->call($this->promotionSortEventListener));
    }

    public function testItCanAddPromotionSortAndPromotionProductsIsInDownWhenInventoryJoinNotExists(): void
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-promotion'],
                'category_code',
                'title'
            ),
            $pagination
        );
        $queryEvent = new ProductSearchQueryEvent(
            DoctrineProductSearchDriver::class,
            new QueryBuilderSearchQuery($this->queryBuilderMock),
            new DoctrineSearchData(
                [],
                ['-visits'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->twice()
                              ->withNoArgs()
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
        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", $buyBoxAlias)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("PARTIAL {$buyBoxAlias}.{id, price, finalPrice, leadTime}")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with(sprintf(
                                   'CASE WHEN %1$s.finalPrice < %1$s.price THEN 1 ELSE 0 END AS HIDDEN promotionSort',
                                   $buyBoxAlias
                               ))
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('promotionSort', 'ASC')
                               ->andReturn($this->queryBuilderMock);

        $this->promotionSortEventListener->onProductSearchDataEvent($event);
        $this->promotionSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->sortData ?? null)->call($this->promotionSortEventListener));
    }
}
