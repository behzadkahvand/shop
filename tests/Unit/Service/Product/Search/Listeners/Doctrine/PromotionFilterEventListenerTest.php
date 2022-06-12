<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\ORM\QueryContext;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\PromotionFilterEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PromotionFilterEventListenerTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    protected $dispatcherMock;
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;
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
     * @var QueryContext|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $contextMock;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->dispatcherMock    = Mockery::mock(EventDispatcherInterface::class);
        $this->websiteAreaMock   = Mockery::mock(WebsiteAreaService::class);
        $this->filterServiceMock = Mockery::mock(QueryBuilderFilterService::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->queryMock         = Mockery::mock(AbstractQuery::class);
        $this->contextMock       = Mockery::mock(QueryContext::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->dispatcherMock    = null;
        $this->websiteAreaMock   = null;
        $this->filterServiceMock = null;
        $this->queryBuilderMock  = null;
        $this->queryMock         = null;
        $this->contextMock       = null;
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $this->dispatcherMock,
            $this->filterServiceMock
        );

        $result = $promotionFilterEventListener::getSubscribedEvents();

        self::assertEquals([ProductSearchDataEvent::class => 'onProductSearchDataEvent'], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $this->dispatcherMock,
            $this->filterServiceMock
        );

        self::assertNull($promotionFilterEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], []),
            new Pagination()
        );

        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $this->dispatcherMock,
            $this->filterServiceMock
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnFalse();

        self::assertNull($promotionFilterEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenPromotionFilterIsNotSet(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], []),
            new Pagination()
        );

        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $this->dispatcherMock,
            $this->filterServiceMock
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        self::assertNull($promotionFilterEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItThrowsExceptionWhenPromotionFilterHasInvalidValue(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([
                'hasPromotion' => 0
            ], []),
            new Pagination()
        );

        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $this->dispatcherMock,
            $this->filterServiceMock
        );

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product promotion filter is invalid!');

        $promotionFilterEventListener->onProductSearchDataEvent($event);
    }

    public function testItCanSetPromotionFilterWhenBuyBoxJoinNotExists(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'hasPromotion' => 1,
                'buyBox.id'    => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'BuyBox';

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturnNull();

        $this->queryBuilderMock->shouldReceive('leftJoin')
                               ->once()
                               ->with("{$rootAlias}.buyBox", 'BuyBox')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('addSelect')
                               ->once()
                               ->with('PARTIAL BuyBox.{id, price, finalPrice, leadTime}')
                               ->andReturn($this->queryBuilderMock);
        $this->queryBuilderMock->shouldReceive('andWhere')
                               ->once()
                               ->with(sprintf('%1$s.finalPrice < %1$s.price', $buyBoxAlias))
                               ->andReturn($this->queryBuilderMock);

        $this->contextMock->shouldReceive('setAlias')
                          ->once()
                          ->with(Product::class, Inventory::class, 'BuyBox')
                          ->andReturn();

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent(
            $this->queryBuilderMock,
            $this->contextMock,
            $rootAlias
        );

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $dispatcher,
            $this->filterServiceMock
        );

        $promotionFilterEventListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();

        self::assertEquals([
            'buyBox.id' => [
                'gt' => 0
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanSetPromotionFilterWhenBuyBoxJoinExists(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'hasPromotion' => 1,
                'buyBox.id'    => [
                    'gt' => 0
                ]
            ], [], 'category_code', 'title'),
            new Pagination()
        );

        $rootAlias   = 'root_alias';
        $buyBoxAlias = 'BuyBox';

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->filterServiceMock->shouldReceive('getJoinAlias')
                                ->once()
                                ->with(Product::class, Inventory::class)
                                ->andReturn($buyBoxAlias);

        $this->queryBuilderMock->shouldReceive('andWhere')
                               ->once()
                               ->with(sprintf('%1$s.finalPrice < %1$s.price', $buyBoxAlias))
                               ->andReturn($this->queryBuilderMock);

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent(
            $this->queryBuilderMock,
            $this->contextMock,
            $rootAlias
        );

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $promotionFilterEventListener = new PromotionFilterEventListener(
            $this->websiteAreaMock,
            $dispatcher,
            $this->filterServiceMock
        );

        $promotionFilterEventListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);

        $data = $event->getData();

        self::assertEquals([
            'buyBox.id' => [
                'gt' => 0
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }
}
