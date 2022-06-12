<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\CustomerDefaultFiltersEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CustomerDefaultFiltersEventListenerTest extends MockeryTestCase
{
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    protected CustomerDefaultFiltersEventListener $defaultFiltersEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);

        $this->defaultFiltersEventListener = new CustomerDefaultFiltersEventListener($this->websiteAreaMock);
    }

    protected function tearDown(): void
    {
        unset($this->websiteAreaMock, $this->defaultFiltersEventListener);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->defaultFiltersEventListener::getSubscribedEvents();

        self::assertEquals([ProductSearchDataEvent::class => ['onProductSearchDataEvent', 2]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->defaultFiltersEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnFalse();

        self::assertNull($this->defaultFiltersEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanSetIsActiveAndStatusesFilters()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([
            'buyBox.finalPrice' => [
                'btn' => '10000,300000'
            ]
        ], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'buyBox.finalPrice' => [
                'btn' => '10000,300000'
            ],
            'status'                                 => [
                'in' => implode(',', [
                    ProductStatusDictionary::SOON,
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::UNAVAILABLE,
                    ProductStatusDictionary::SHUTDOWN,
                ])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertNull($data->getTitle());
    }

    public function testItCanSetIsActiveAndInventoryIdFilters()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([
            'status' => [
                'in' => implode(',', [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::SOON])
            ]
        ], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'buyBox.id' => [
                'gt' => 0
            ],
            'status'                         => [
                'in' => implode(',', [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::SOON])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertNull($data->getTitle());
    }

    public function testItCanSetIsActiveAndStatusesAndInventoryIdFiltersWhenTitleFilterDoesNotSet()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'buyBox.id' => [
                'gt' => 0
            ],
            'status'                         => [
                'in' => implode(',', [
                    ProductStatusDictionary::SOON,
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::UNAVAILABLE,
                    ProductStatusDictionary::SHUTDOWN,
                ])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanSetIsActiveAndStatusesAndInventoryIdFiltersWhenTitleFilterArraySet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['title' => ['like' => 'mobile']], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'buyBox.id' => [
                'gt' => 0
            ],
            'status'                         => [
                'in' => implode(',', [
                    ProductStatusDictionary::SOON,
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::UNAVAILABLE,
                    ProductStatusDictionary::SHUTDOWN,
                ])
            ]
        ], $data->getFilters());
        self::assertNull($data->getCategoryCode());
        self::assertEquals('mobile', $data->getTitle());
    }

    public function testItCanSetIsActiveAndStatusesAndInventoryIdFiltersWhenTitleFilterSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['title' => 'mobile'], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'buyBox.id' => [
                'gt' => 0
            ],
            'status'                         => [
                'in' => implode(',', [
                    ProductStatusDictionary::SOON,
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::UNAVAILABLE,
                    ProductStatusDictionary::SHUTDOWN,
                ])
            ]
        ], $data->getFilters());
        self::assertNull($data->getCategoryCode());
        self::assertEquals('mobile', $data->getTitle());
    }
}
