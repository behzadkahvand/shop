<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\ORM\Events\QueryBuilderFilterApplyingEvent;
use App\Service\ORM\QueryContext;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\DefaultOrderByListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Tests\Controller\FunctionalTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DefaultOrderByListenerTest extends FunctionalTestCase
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
        unset($this->websiteAreaMock, $this->dispatcherMock);
    }

    public function testItCanGetSubscribedEvents()
    {
        $defaultOrderByListener = new DefaultOrderByListener($this->dispatcherMock);

        $result = $defaultOrderByListener::getSubscribedEvents();

        self::assertEquals([
            ProductSearchDataEvent::class       => 'onProductSearchDataEvent',
            SellerProductSearchDataEvent::class => 'onProductSearchDataEvent',
        ], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        $defaultOrderByListener = new DefaultOrderByListener($this->dispatcherMock);

        self::assertNull($defaultOrderByListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanAddOrderByToQueryBuilder()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], []),
            new Pagination()
        );

        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryContextMock = Mockery::mock(QueryContext::class);
        $rootAlias        = 'root_alias';

        $statuses = [
            ProductStatusDictionary::CONFIRMED,
            ProductStatusDictionary::SOON,
            ProductStatusDictionary::UNAVAILABLE,
            ProductStatusDictionary::SHUTDOWN,
        ];

        $queryBuilderMock->shouldReceive('addOrderBy')
                         ->once()
                         ->with(sprintf('FIELD(%s.status, \'%s\')', $rootAlias, implode("', '", $statuses)))
                         ->andReturn($queryBuilderMock);

        $queryBuilderEvent = new QueryBuilderFilterApplyingEvent($queryBuilderMock, $queryContextMock, $rootAlias);

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $defaultOrderByListener = new DefaultOrderByListener($dispatcher);
        $defaultOrderByListener->onProductSearchDataEvent($event);

        $dispatcher->dispatch($queryBuilderEvent);
    }
}
