<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\SellerSearchDataValidationEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerSearchDataValidationEventListenerTest extends MockeryTestCase
{
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    protected SellerSearchDataValidationEventListener $searchDataValidationEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);

        $this->searchDataValidationEventListener = new SellerSearchDataValidationEventListener($this->websiteAreaMock);
    }

    protected function tearDown(): void
    {
        unset($this->websiteAreaMock, $this->searchDataValidationEventListener);
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

    public function testItDoNothingWhenWebsiteAreaIsNotSeller()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnFalse();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItThrowsSearchDataValidationExceptionWhenBrandCodeFilterIsSet()
    {
        $this->expectException(SearchDataValidationException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Product filters is invalid!');

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(['brand.code' => 'code_1'], []),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
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
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $this->searchDataValidationEventListener->onProductSearchData($event);
    }

    public function testItDoNothingWhenFiltersAndSortsAreEmpty()
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenBrandIdFilterIsSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                ['brand.id' => 1],
                []
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals(['brand.id' => 1], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenProductTitleFilterIsSet()
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new SearchData(
                [
                    'title' => [
                        'like' => '%Ltd%'
                    ]
                ],
                []
            ),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([
            'title' => [
                'like' => '%Ltd%'
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenCategoryCodeAndProductOriginalityFilterAndNoSortAreSet()
    {
        $event = new ProductSearchDataEvent(
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
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        self::assertNull($this->searchDataValidationEventListener->onProductSearchData($event));

        $data = $event->getData();

        self::assertEquals([
            'category.code' => 'category_code',
            'isOriginal'    => 1
        ], $data->getFilters());
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
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
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
                              ->with(WebsiteAreaDictionary::AREA_SELLER)
                              ->andReturnTrue();

        $this->searchDataValidationEventListener->onProductSearchData($event);
    }
}
