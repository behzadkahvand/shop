<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchQueryEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\CustomerPriceSortEventListener;
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

class CustomerPriceSortEventListenerTest extends FunctionalTestCase
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

    protected CustomerPriceSortEventListener $customerPriceSortEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->websiteAreaMock   = Mockery::mock(WebsiteAreaService::class);
        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class, [$this->em]);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);

        $this->customerPriceSortEventListener = new CustomerPriceSortEventListener(
            $this->websiteAreaMock,
            $this->filterServiceMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->customerPriceSortEventListener,
            $this->filterServiceMock,
            $this->websiteAreaMock,
            $this->em,
            $this->queryBuilderMock,
            $this->queryMock,
        );
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->customerPriceSortEventListener::getSubscribedEvents();

        self::assertEquals([
            ProductSearchDataEvent::class  => 'onProductSearchDataEvent',
            ProductSearchQueryEvent::class => ['onProductSearchQueryEvent', 100],
        ], $result);
    }

    public function testItDoNothingWhenDriverIsInvalidForSearchDataEvent()
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->customerPriceSortEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomerForSearchDataEvent()
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

        self::assertNull($this->customerPriceSortEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingPriceSortNotSetForSearchDataEvent()
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

        self::assertNull($this->customerPriceSortEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
    }

    public function testItCanSetApplyMaximumPriceSortInSearchDataEvent()
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "buyBox.finalPrice",
            "direction"        => "DESC",
            "direction_prefix" => "-"
        ], (fn() => $this->priceSort)->call($this->customerPriceSortEventListener));

        self::assertTrue((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanSetApplyMinimumPriceSortInSearchDataEvent()
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'buyBox.finalPrice'],
                'category_code',
                'title'
            ),
            $pagination
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "buyBox.finalPrice",
            "direction"        => "ASC",
            "direction_prefix" => ""
        ], (fn() => $this->priceSort)->call($this->customerPriceSortEventListener));

        self::assertTrue((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItDoNothingPriceSortNotSetForSearchQueryEvent()
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

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);

        self::assertNull($this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent));

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItDoNothingWhenDriverIsInvalidForSearchQueryEvent()
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
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

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);

        self::assertNull($this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent));

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "buyBox.finalPrice",
            "direction"        => "DESC",
            "direction_prefix" => "-"
        ], (fn() => $this->priceSort)->call($this->customerPriceSortEventListener));
        self::assertTrue((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomerForSearchQueryEvent()
    {
        $pagination = new Pagination();
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
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

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);

        self::assertNull($this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent));

        $data = $queryEvent->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertEquals([
            "field"            => "buyBox.finalPrice",
            "direction"        => "DESC",
            "direction_prefix" => "-"
        ], (fn() => $this->priceSort)->call($this->customerPriceSortEventListener));
        self::assertTrue((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMaximumPriceSortWhenInventoryJoinExistsAndItHasProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'inventory_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($inventoryAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'DESC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('groupBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id IN(:productIds)")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with(['productIds' => [3, 10, 5]])
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('orderBy')
                               ->once()
                               ->with(sprintf('FIELD(%s.id, \'%s\')', $rootAlias, implode("','", [3, 10, 5])))
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([
                            ['id' => 3],
                            ['id' => 10],
                            ['id' => 5],
                        ]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMaximumPriceSortWhenInventoryJoinExistsAndItHasNotProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'inventory_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($inventoryAlias);
        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'DESC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('orderBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id < 0")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with([])
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $event->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMaximumPriceSortWhenInventoryJoinNotExistsAndItHasProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'BuyBox';

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
                               ->with("{$rootAlias}.buyBox", $inventoryAlias)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'DESC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('groupBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id IN(:productIds)")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with(['productIds' => [3, 10, 5]])
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('orderBy')
                               ->once()
                               ->with(sprintf('FIELD(%s.id, \'%s\')', $rootAlias, implode("','", [3, 10, 5])))
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([
                            ['id' => 3],
                            ['id' => 10],
                            ['id' => 5],
                        ]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMaximumPriceSortWhenInventoryJoinNotExistsAndItHasNotProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', '-buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'BuyBox';

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
                               ->with("{$rootAlias}.buyBox", $inventoryAlias)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'DESC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('orderBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id < 0")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with([])
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMinimumPriceSortWhenInventoryJoinExistsAndItHasProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'inventory_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($inventoryAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'ASC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('groupBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id IN(:productIds)")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with(['productIds' => [3, 10, 5]])
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('orderBy')
                               ->once()
                               ->with(sprintf('FIELD(%s.id, \'%s\')', $rootAlias, implode("','", [3, 10, 5])))
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([
                            ['id' => 3],
                            ['id' => 10],
                            ['id' => 5],
                        ]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMinimumPriceSortWhenInventoryJoinExistsAndItHasNotProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'inventory_alias';

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($inventoryAlias);

        $this->queryBuilderMock->shouldReceive('getRootAliases')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([$rootAlias]);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'ASC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('orderBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id < 0")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with([])
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                        ->once()
                        ->withNoArgs()
                        ->andReturn([]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMinimumPriceSortWhenInventoryJoinNotExistsAndItHasProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'BuyBox';

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
                               ->with("{$rootAlias}.buyBox", $inventoryAlias)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'ASC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('groupBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id IN(:productIds)")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with(['productIds' => [3, 10, 5]])
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('orderBy')
                               ->once()
                               ->with(sprintf('FIELD(%s.id, \'%s\')', $rootAlias, implode("','", [3, 10, 5])))
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                  ->once()
                  ->withNoArgs()
                  ->andReturn([
                      ['id' => 3],
                      ['id' => 10],
                      ['id' => 5],
                  ]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }

    public function testItCanAddMinimumPriceSortWhenInventoryJoinNotExistsAndItHasNotProductIds()
    {
        $pagination = new Pagination(2);
        $event      = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(
                [],
                ['-visits', 'buyBox.finalPrice'],
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

        $rootAlias      = 'root_alias';
        $inventoryAlias = 'BuyBox';

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
                               ->with("{$rootAlias}.buyBox", $inventoryAlias)
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('select')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with("COALESCE(MIN({$inventoryAlias}.finalPrice), 0) AS HIDDEN priceSort")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addOrderBy')
                               ->once()
                               ->with('priceSort', 'ASC')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('groupBy')
                               ->once()
                               ->with("{$rootAlias}.id")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setFirstResult')
                               ->once()
                               ->with($pagination->getOffset())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setMaxResults')
                               ->once()
                               ->with($pagination->getLimit())
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('getQuery')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->queryMock);
        $this->queryBuilderMock->shouldReceive('resetDQLPart')
                               ->once()
                               ->with('orderBy')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('where')
                               ->once()
                               ->with("{$rootAlias}.id < 0")
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('setParameters')
                               ->once()
                               ->with([])
                               ->andReturn($this->queryBuilderMock);

        $this->queryMock->shouldReceive('getScalarResult')
                  ->once()
                  ->withNoArgs()
                  ->andReturn([]);

        $this->customerPriceSortEventListener->onProductSearchDataEvent($event);
        $this->customerPriceSortEventListener->onProductSearchQueryEvent($queryEvent);

        $data = $queryEvent->getData();

        self::assertEquals(1, $event->getPagination()->getPage());
        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
        self::assertNull((fn() => $this->priceSort ?? null)->call($this->customerPriceSortEventListener));
        self::assertFalse((fn() => $this->shouldApplyPriceSort)->call($this->customerPriceSortEventListener));
    }
}
