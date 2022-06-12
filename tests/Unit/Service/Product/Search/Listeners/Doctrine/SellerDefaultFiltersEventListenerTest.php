<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\SellerDefaultFiltersEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerDefaultFiltersEventListenerTest extends MockeryTestCase
{
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    protected SellerDefaultFiltersEventListener $defaultFiltersEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);

        $this->defaultFiltersEventListener = new SellerDefaultFiltersEventListener($this->websiteAreaMock);
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

    public function testItDoNothingWhenWebsiteAreaIsNotSeller()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnFalse();

        self::assertNull($this->defaultFiltersEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanSetStatusesFiltersWhenTitleArrayFilterSet()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([
            'title' => [
                'like' => 'test'
            ]
        ], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'status' => [
                'in' => implode(',', [
                    ProductStatusDictionary::SOON,
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::UNAVAILABLE,
                ])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertEquals('test', $data->getTitle());
    }

    public function testItThrowsSearchDataValidationExceptionWhenStatusFilterIsInvalid()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product status filter is invalid!');

        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([
            'status' => ProductStatusDictionary::DRAFT
        ], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);
    }

    public function testItCanSetStatusesFiltersWhenStatusFilterIsSet()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([
            'status' => ProductStatusDictionary::SOON
        ], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'status' => ProductStatusDictionary::SOON
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertNull($data->getTitle());
    }

    public function testItCanSetStatusesFiltersWhenTitleFilterSet()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([
            'title' => 'test'
        ], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $this->defaultFiltersEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertInstanceOf(DoctrineSearchData::class, $data);
        self::assertEquals([
            'status' => [
                'in' => implode(',', [
                    ProductStatusDictionary::SOON,
                    ProductStatusDictionary::CONFIRMED,
                    ProductStatusDictionary::UNAVAILABLE,
                ])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertNull($data->getCategoryCode());
        self::assertEquals('test', $data->getTitle());
    }
}
