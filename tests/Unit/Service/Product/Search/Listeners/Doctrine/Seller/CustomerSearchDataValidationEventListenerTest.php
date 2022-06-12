<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\Seller\CustomerSearchDataValidationEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class CustomerSearchDataValidationEventListenerTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|WebsiteAreaService|null $websiteAreaMock;

    protected ?CustomerSearchDataValidationEventListener $searchDataValidationEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);

        $this->searchDataValidationEventListener = new CustomerSearchDataValidationEventListener($this->websiteAreaMock);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->searchDataValidationEventListener::getSubscribedEvents();

        self::assertEquals([SellerProductSearchDataEvent::class => ['onProductSearchData', 100]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $event = new SellerProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

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

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItThrowsSearchDataValidationExceptionWhenBrandCodeFilterIsArray()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Only valid operator for "brand.code" filter is equality!');

        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['brand.code' => ['in' => 'code_1,code_2']], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->searchDataValidationEventListener->onProductSearchData($event);
    }

    public function testItThrowsSearchDataValidationExceptionWhenCategoryCodeFilterIsArray()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Only valid operator for "category.code" filter is equality!');

        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['category.code' => ['in' => 'code_1,code_2']], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->searchDataValidationEventListener->onProductSearchData($event);
    }

    public function testItDoNothingWhenFiltersAndSortsAreEmpty()
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

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenFinalPriceFilterAndSortAreSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'productVariants.inventories.finalPrice' => [
                        'btn' => '10000,300000'
                    ]
                ],
                [
                    '-productVariants.inventories.finalPrice'
                ]
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([
            'productVariants.inventories.finalPrice' => [
                'btn' => '10000,300000'
            ]
        ], $data->getFilters());
        self::assertEquals([
            '-productVariants.inventories.finalPrice'
        ], $data->getSorts());
    }

    public function testItDoNothingWhenBrandIdFilterAndNewestProductSortAreSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                ['brand.id' => 1],
                [
                    '-productVariants.inventories.createdAt'
                ]
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals(['brand.id' => 1], $data->getFilters());
        self::assertEquals([
            '-productVariants.inventories.createdAt'
        ], $data->getSorts());
    }

    public function testItDoNothingWhenProductTitleFilterAndProductVisitsSortAreSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'title' => [
                        'like' => '%Ltd%'
                    ]
                ],
                ['-visits']
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([
            'title' => [
                'like' => '%Ltd%'
            ]
        ], $data->getFilters());
        self::assertEquals(['-visits'], $data->getSorts());
    }

    public function testItDoNothingWhenBrandCodeAndAvailableProductFilterAndBestSellersProductSortAreSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'brand.code'  => 'brand_code',
                    'isAvailable' => true,
                ],
                ['-orderCount']
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([
            'brand.code'  => 'brand_code',
            'isAvailable' => true,
        ], $data->getFilters());
        self::assertEquals(['-orderCount'], $data->getSorts());
    }

    public function testItDoNothingWhenCategoryCodeAndProductOriginalityFilterAndNoSortAreSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'category.code' => 'category_code',
                    'isOriginal'    => 1
                ],
                []
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([
            'category.code' => 'category_code',
            'isOriginal'    => 1
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenFastestDeliverySortAndNoFilterAreSet()
    {
        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [],
                [
                    '-productVariants.inventories.leadTime'
                ]
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([
            '-productVariants.inventories.leadTime'
        ], $data->getSorts());
    }

    public function testItThrowsSearchDataValidationExceptionOnInvalidFilter()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product filters is invalid!');

        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['invalid' => 'value'], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->searchDataValidationEventListener->onProductSearchData($event);
    }

    public function testItThrowsSearchDataValidationExceptionOnInvalidSort()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product sorts is invalid!');

        $event = new SellerProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData([], ['-invalid']),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->searchDataValidationEventListener->onProductSearchData($event);
    }
}
