<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryContext;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\StatusFilterListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StatusFilterListenerTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    protected $dispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->dispatcherMock = Mockery::mock(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->dispatcherMock);
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $statusFilterListener = new StatusFilterListener($this->dispatcherMock);

        $result = $statusFilterListener::getSubscribedEvents();

        self::assertEquals([ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        $statusFilterListener = new StatusFilterListener($this->dispatcherMock);

        self::assertNull($statusFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenInventoryFinalPriceFilterIsSet(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([
                'buyBox.finalPrice' => [
                    'gte' => 500000
                ]
            ], []),
            new Pagination()
        );

        $statusFilterListener = new StatusFilterListener($this->dispatcherMock);

        self::assertNull($statusFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([
            'buyBox.finalPrice' => [
                'gte' => 500000
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenConfirmedStatusFilterIsSet(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([
                'status' => [
                    'in' => implode(',', [
                        ProductStatusDictionary::CONFIRMED
                    ])
                ]
            ], []),
            new Pagination()
        );

        $statusFilterListener = new StatusFilterListener($this->dispatcherMock);

        self::assertNull($statusFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([
            'status' => [
                'in' => implode(',', [
                    ProductStatusDictionary::CONFIRMED
                ])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanUpdateForSoonAndUnavailableStatus(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'    => [
                    'in' => implode(',', [
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::UNAVAILABLE,
                        ProductStatusDictionary::SHUTDOWN,
                    ])
                ],
                'buyBox.id' => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryContextMock = Mockery::mock(QueryContext::class);
        $rootAlias        = 'root_alias';

        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with("{$rootAlias}.buyBox", 'BuyBox')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL BuyBox.{id, price, finalPrice, leadTime}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:statuses) AND 0 < BuyBox.id) OR (%1$s.status IN(:unsetStatuses))',
                             $rootAlias
                         ))
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('statuses', [
                             ProductStatusDictionary::CONFIRMED,
                             ProductStatusDictionary::SHUTDOWN,
                         ])
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('unsetStatuses', [
                             ProductStatusDictionary::SOON,
                             ProductStatusDictionary::UNAVAILABLE
                         ])
                         ->andReturn($queryBuilderMock);

        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(Product::class, Inventory::class, 'BuyBox')
                         ->andReturn();

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent($queryBuilderMock, $queryContextMock, $rootAlias);

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $statusFilterListener = new StatusFilterListener($dispatcher);
        $statusFilterListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanUpdateForSoonStatus(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'    => [
                    'in' => implode(',', [
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::CONFIRMED
                    ])
                ],
                'buyBox.id' => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryContextMock = Mockery::mock(QueryContext::class);
        $rootAlias        = 'root_alias';

        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with("{$rootAlias}.buyBox", 'BuyBox')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL BuyBox.{id, price, finalPrice, leadTime}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:statuses) AND 0 < BuyBox.id) OR (%1$s.status IN(:unsetStatuses))',
                             $rootAlias
                         ))
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('statuses', [
                             ProductStatusDictionary::CONFIRMED
                         ])
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('unsetStatuses', [
                             ProductStatusDictionary::SOON
                         ])
                         ->andReturn($queryBuilderMock);

        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(Product::class, Inventory::class, 'BuyBox')
                         ->andReturn();

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent($queryBuilderMock, $queryContextMock, $rootAlias);

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $statusFilterListener = new StatusFilterListener($dispatcher);
        $statusFilterListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanUpdateForUnavailableStatus(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'    => [
                    'in' => implode(',', [
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::UNAVAILABLE,
                    ])
                ],
                'buyBox.id' => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryContextMock = Mockery::mock(QueryContext::class);
        $rootAlias        = 'root_alias';

        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with("{$rootAlias}.buyBox", 'BuyBox')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL BuyBox.{id, price, finalPrice, leadTime}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:statuses) AND 0 < BuyBox.id) OR (%1$s.status IN(:unsetStatuses))',
                             $rootAlias
                         ))
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('statuses', [
                             ProductStatusDictionary::CONFIRMED,
                         ])
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('unsetStatuses', [
                             ProductStatusDictionary::UNAVAILABLE
                         ])
                         ->andReturn($queryBuilderMock);

        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(Product::class, Inventory::class, 'BuyBox')
                         ->andReturn();

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent($queryBuilderMock, $queryContextMock, $rootAlias);

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $statusFilterListener = new StatusFilterListener($dispatcher);
        $statusFilterListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanUpdateWithoutSoonAndUnavailableStatus(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'    => [
                    'in' => implode(',', [
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::SHUTDOWN,
                    ])
                ],
                'buyBox.id' => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryContextMock = Mockery::mock(QueryContext::class);
        $rootAlias        = 'root_alias';

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent($queryBuilderMock, $queryContextMock, $rootAlias);

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $statusFilterListener = new StatusFilterListener($dispatcher);
        $statusFilterListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();
        self::assertEquals([
            'status'    => [
                'in' => implode(',', [
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::SHUTDOWN,
                ])
            ],
            'buyBox.id' => [
                'gt' => 0
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanUpdateForJustSoonAndUnavailableStatus(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'    => [
                    'in' => implode(',', [
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::UNAVAILABLE,
                    ])
                ],
                'buyBox.id' => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryContextMock = Mockery::mock(QueryContext::class);
        $rootAlias        = 'root_alias';

        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with("{$rootAlias}.buyBox", 'BuyBox')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL BuyBox.{id, price, finalPrice, leadTime}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:unsetStatuses))',
                             $rootAlias
                         ))
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('setParameter')
                         ->once()
                         ->with('unsetStatuses', [
                             ProductStatusDictionary::SOON,
                             ProductStatusDictionary::UNAVAILABLE
                         ])
                         ->andReturn($queryBuilderMock);

        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(Product::class, Inventory::class, 'BuyBox')
                         ->andReturn();

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent($queryBuilderMock, $queryContextMock, $rootAlias);

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $statusFilterListener = new StatusFilterListener($dispatcher);
        $statusFilterListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }
}
