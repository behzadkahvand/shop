<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\Listeners\Doctrine\Seller\FilterAndSortMappingEventListener;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\SearchDataMapping\Adapters\StaticListSellerSearchDataMappingAdapter;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

final class FilterAndSortMappingEventListenerTest extends BaseUnitTestCase
{
    public function testItGetSubscribedEvents(): void
    {
        self::assertEquals(
            [SellerProductSearchDataEvent::class => ['onProductSearchData', 110]],
            FilterAndSortMappingEventListener::getSubscribedEvents()
        );
    }

    public function testItDontProcessEmptyFilterAndSorts(): void
    {
        $listener = new FilterAndSortMappingEventListener(
            Mockery::mock(WebsiteAreaService::class),
            Mockery::mock(StaticListSellerSearchDataMappingAdapter::class)
        );

        $searchData = new SearchData([], []);
        $event      = new ProductSearchDataEvent('driver', $searchData, new Pagination());

        $listener->onProductSearchData($event);

        self::assertEmpty($event->getData()->getFilters());
        self::assertEmpty($event->getData()->getSorts());
    }

    public function testItThrowExceptionIfFilterIsInvalid(): void
    {
        $invalidFilter = 'invalidFilter';
        $area          = WebsiteAreaDictionary::AREA_CUSTOMER;

        $areaService = Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('getArea')->once()->withNoArgs()->andReturn($area);

        $mapping = Mockery::mock(StaticListSellerSearchDataMappingAdapter::class);
        $mapping->shouldReceive('hasMappedFilter')->once()->with($invalidFilter, $area)->andReturnFalse();

        $listener = new FilterAndSortMappingEventListener($areaService, $mapping);

        $searchData = new SearchData([$invalidFilter => 1], []);
        $event      = new ProductSearchDataEvent('driver', $searchData, new Pagination());

        self::expectException(SearchDataValidationException::class);
        self::expectExceptionMessage('Product filters is invalid!');

        $listener->onProductSearchData($event);
    }

    public function testItMapValidFilter(): void
    {
        $mappedValidFilter = 'mappedValidFilter';
        $validFilter       = 'validFilter';
        $area              = WebsiteAreaDictionary::AREA_CUSTOMER;

        $areaService = Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('getArea')->once()->withNoArgs()->andReturn($area);

        $mapping = Mockery::mock(StaticListSellerSearchDataMappingAdapter::class);
        $mapping->shouldReceive('hasMappedFilter')->once()->with($validFilter, $area)->andReturnTrue();
        $mapping->shouldReceive('getMappedFilter')->once()->with($validFilter, $area)->andReturn('mappedValidFilter');

        $listener = new FilterAndSortMappingEventListener($areaService, $mapping);

        $searchData = new SearchData([$validFilter => 1], []);
        $event      = new ProductSearchDataEvent('driver', $searchData, new Pagination());

        $listener->onProductSearchData($event);

        self::assertNotEquals($searchData->getFilters(), $event->getData()->getFilters());
        self::assertEquals([$mappedValidFilter => 1], $event->getData()->getFilters());
    }

    public function testItThrowExceptionIfSortIsInvalid(): void
    {
        $invalidSort = 'invalidSort';
        $area        = WebsiteAreaDictionary::AREA_CUSTOMER;

        $areaService = Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('getArea')->once()->withNoArgs()->andReturn($area);

        $mapping = Mockery::mock(StaticListSellerSearchDataMappingAdapter::class);
        $mapping->shouldReceive('hasMappedSort')->once()->with($invalidSort, $area)->andReturnFalse();

        $listener = new FilterAndSortMappingEventListener($areaService, $mapping);

        $searchData = new SearchData([], [$invalidSort]);
        $event      = new ProductSearchDataEvent('driver', $searchData, new Pagination());

        self::expectException(SearchDataValidationException::class);
        self::expectExceptionMessage('Product sorts is invalid!');

        $listener->onProductSearchData($event);
    }

    public function testItMapValidSort(): void
    {
        $mappedValidSort = 'mappedValidSort';
        $validSort       = 'validSort';
        $area            = WebsiteAreaDictionary::AREA_CUSTOMER;

        $areaService = Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('getArea')->once()->withNoArgs()->andReturn($area);

        $mapping = Mockery::mock(StaticListSellerSearchDataMappingAdapter::class);
        $mapping->shouldReceive('hasMappedSort')->once()->with($validSort, $area)->andReturnTrue();
        $mapping->shouldReceive('getMappedSort')->once()->with($validSort, $area)->andReturn($mappedValidSort);

        $listener = new FilterAndSortMappingEventListener($areaService, $mapping);

        $searchData = new SearchData([], [$validSort]);
        $event      = new ProductSearchDataEvent('driver', $searchData, new Pagination());

        $listener->onProductSearchData($event);

        self::assertNotEquals($searchData->getSorts(), $event->getData()->getSorts());
        self::assertEquals([$mappedValidSort], $event->getData()->getSorts());
    }
}
