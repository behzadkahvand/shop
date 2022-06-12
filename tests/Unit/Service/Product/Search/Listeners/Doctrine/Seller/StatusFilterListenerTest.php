<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryContext;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\Seller\StatusFilterListener;
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

        self::assertEquals([SellerProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $event = new SellerProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        $statusFilterListener = new StatusFilterListener($this->dispatcherMock);

        self::assertNull($statusFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenInventoryFinalPriceFilterIsSet(): void
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([
                'productVariants.inventories.finalPrice' => [
                    'gte' => 500000
                ]
            ], []),
            new Pagination()
        );

        $statusFilterListener = new StatusFilterListener($this->dispatcherMock);

        self::assertNull($statusFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([
            'productVariants.inventories.finalPrice' => [
                'gte' => 500000
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenConfirmedStatusFilterIsSet(): void
    {
        $event = new SellerProductSearchDataEvent(
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
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'                         => [
                    'in' => implode(',', [
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::UNAVAILABLE,
                        ProductStatusDictionary::SHUTDOWN,
                    ])
                ],
                'productVariants.inventories.id' => [
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
                         ->with("{$rootAlias}.productVariants", 'ProductVariants')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL ProductVariants.{id}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with('ProductVariants.inventories', 'Inventories')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:statuses) AND 0 < Inventories.id) OR (%1$s.status IN(:unsetStatuses))',
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
                         ->with(Product::class, ProductVariant::class, 'ProductVariants')
                         ->andReturn();
        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(ProductVariant::class, Inventory::class, 'Inventories')
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
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'                         => [
                    'in' => implode(',', [
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::CONFIRMED
                    ])
                ],
                'productVariants.inventories.id' => [
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
                         ->with("{$rootAlias}.productVariants", 'ProductVariants')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL ProductVariants.{id}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with('ProductVariants.inventories', 'Inventories')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:statuses) AND 0 < Inventories.id) OR (%1$s.status IN(:unsetStatuses))',
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
                         ->with(Product::class, ProductVariant::class, 'ProductVariants')
                         ->andReturn();
        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(ProductVariant::class, Inventory::class, 'Inventories')
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
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'                         => [
                    'in' => implode(',', [
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::UNAVAILABLE,
                    ])
                ],
                'productVariants.inventories.id' => [
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
                         ->with("{$rootAlias}.productVariants", 'ProductVariants')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL ProductVariants.{id}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with('ProductVariants.inventories', 'Inventories')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('andWhere')
                         ->once()
                         ->with(sprintf(
                             '(%1$s.status IN(:statuses) AND 0 < Inventories.id) OR (%1$s.status IN(:unsetStatuses))',
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
                         ->with(Product::class, ProductVariant::class, 'ProductVariants')
                         ->andReturn();
        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(ProductVariant::class, Inventory::class, 'Inventories')
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
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'                         => [
                    'in' => implode(',', [
                        ProductStatusDictionary::CONFIRMED,
                        ProductStatusDictionary::SHUTDOWN,
                    ])
                ],
                'productVariants.inventories.id' => [
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
            'status'                         => [
                'in' => implode(',', [
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::SHUTDOWN,
                ])
            ],
            'productVariants.inventories.id' => [
                'gt' => 0
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanUpdateForJustSoonAndUnavailableStatus(): void
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'status'                         => [
                    'in' => implode(',', [
                        ProductStatusDictionary::SOON,
                        ProductStatusDictionary::UNAVAILABLE,
                    ])
                ],
                'productVariants.inventories.id' => [
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
                         ->with("{$rootAlias}.productVariants", 'ProductVariants')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL ProductVariants.{id}')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('leftJoin')
                         ->once()
                         ->with('ProductVariants.inventories', 'Inventories')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('addSelect')
                         ->once()
                         ->with('PARTIAL Inventories.{id, price, finalPrice, leadTime, isActive, status, sellerStock}')
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
                         ->with(Product::class, ProductVariant::class, 'ProductVariants')
                         ->andReturn();
        $queryContextMock->shouldReceive('setAlias')
                         ->once()
                         ->with(ProductVariant::class, Inventory::class, 'Inventories')
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
