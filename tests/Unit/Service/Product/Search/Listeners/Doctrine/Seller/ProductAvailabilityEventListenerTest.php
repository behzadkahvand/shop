<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\ProductStatusDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\Seller\ProductAvailabilityEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductAvailabilityEventListenerTest extends MockeryTestCase
{
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    protected ProductAvailabilityEventListener $productAvailabilityEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);

        $this->productAvailabilityEventListener = new ProductAvailabilityEventListener($this->websiteAreaMock);
    }

    protected function tearDown(): void
    {
        unset($this->websiteAreaMock, $this->productAvailabilityEventListener);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->productAvailabilityEventListener::getSubscribedEvents();

        self::assertEquals([SellerProductSearchDataEvent::class => ['onProductSearchQueryEvent', 3]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $event = new SellerProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->productAvailabilityEventListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnFalse();

        self::assertNull($this->productAvailabilityEventListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenIsAvailableFilterIsNotSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->productAvailabilityEventListener->onProductSearchQueryEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItThrowsSearchDataValidationExceptionWhenIsAvailableFilterIsFalse()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['isAvailable' => false], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product availability filter is invalid!');

        self::assertNull($this->productAvailabilityEventListener->onProductSearchQueryEvent($event));
    }

    public function testItCanSetStatusesFilter()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['isAvailable' => true], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->productAvailabilityEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals([
            'status' => [
                'in' => implode(',', [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::SOON])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanSetStatusesFilterWhenFiltersHasProductPrice()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'productVariants.inventories.finalPrice' => [
                        'gte' => '100000',
                        'lte' => '600000'
                    ]
                ],
                []
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->productAvailabilityEventListener->onProductSearchQueryEvent($event);

        $data = $event->getData();

        self::assertEquals([
            'productVariants.inventories.finalPrice' => [
                'gte' => '100000',
                'lte' => '600000'
            ],
            'status' => [
                'in' => implode(',', [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::SOON])
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }
}
