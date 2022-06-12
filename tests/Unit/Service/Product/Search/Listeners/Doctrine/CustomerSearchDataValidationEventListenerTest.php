<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\CustomerSearchDataValidationEventListener;
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

        self::assertEquals([ProductSearchDataEvent::class => ['onProductSearchData', 100]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid()
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

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

        $event = new ProductSearchDataEvent(
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

        $event = new ProductSearchDataEvent(
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
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

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
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'buyBox.finalPrice' => [
                        'btn' => '10000,300000'
                    ]
                ],
                [
                    '-buyBox.finalPrice'
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
            'buyBox.finalPrice' => [
                'btn' => '10000,300000'
            ]
        ], $data->getFilters());
        self::assertEquals([
            '-buyBox.finalPrice'
        ], $data->getSorts());
    }

    public function testItDoNothingWhenBrandIdFilterAndNewestProductSortAreSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                ['brand.id' => 1],
                [
                    '-buyBox.createdAt'
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
            '-buyBox.createdAt'
        ], $data->getSorts());
    }

    public function testItDoNothingWhenProductTitleFilterAndProductVisitsSortAreSet()
    {
        $event = new ProductSearchDataEvent(
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
        $event = new ProductSearchDataEvent(
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

    public function testItDoNothingWhenCategoryCodeAndProductOriginalityFilterAndPromotionSortAreSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'category.code' => 'category_code',
                    'isOriginal'    => 1
                ],
                ['promotion']
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
        self::assertEquals(['promotion'], $data->getSorts());
    }

    public function testItDoNothingWhenFastestDeliverySortAndNoFilterAreSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [],
                [
                    '-buyBox.leadTime'
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
            '-buyBox.leadTime'
        ], $data->getSorts());
    }

    public function testItDoNothingWhenPromotionFilterAndNoSortAreSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'hasPromotion' => 1
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

        self::assertEquals(['hasPromotion' => 1], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItThrowsSearchDataValidationExceptionOnInvalidFilter()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product filters is invalid!');

        $event = new ProductSearchDataEvent(
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

        $event = new ProductSearchDataEvent(
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
