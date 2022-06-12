<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\CustomerDefaultSortsEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CustomerDefaultSortsEventListenerTest extends MockeryTestCase
{
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    protected ?CustomerDefaultSortsEventListener $defaultSortsEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);

        $this->defaultSortsEventListener = new CustomerDefaultSortsEventListener($this->websiteAreaMock);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->defaultSortsEventListener::getSubscribedEvents();

        self::assertEquals([ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->defaultSortsEventListener->onProductSearchDataEvent($event));

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

        self::assertNull($this->defaultSortsEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenSortsIsSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([], ['-promotion'], null, 'title'),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->defaultSortsEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-promotion'], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    public function testItCanSetDefaultSorts()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([], [], null, 'title'),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->defaultSortsEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);

        self::assertEquals([], $data->getFilters());
        self::assertEquals(['-orderCount'], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->websiteAreaMock           = null;
        $this->defaultSortsEventListener = null;
    }
}
