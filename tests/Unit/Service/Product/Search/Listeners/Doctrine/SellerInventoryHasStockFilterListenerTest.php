<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchResultEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\SellerInventoryHasStockFilterListener;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\SearchResult;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerInventoryHasStockFilterListenerTest
 */
final class SellerInventoryHasStockFilterListenerTest extends MockeryTestCase
{
    public function testGettingSubscribedEvents(): void
    {
        self::assertEquals([
            ProductSearchDataEvent::class   => ['onProductSearchDataEvent', 1000],
            ProductSearchResultEvent::class => 'onProductSearchResultEvent',
        ], SellerInventoryHasStockFilterListener::getSubscribedEvents());
    }

    public function testItSkipIfDriverIsNotDoctrine(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $websiteArea   = \Mockery::mock(WebsiteAreaService::class);
        $websiteArea->shouldNotReceive('isSellerArea');

        $listener = new SellerInventoryHasStockFilterListener($entityManager, $websiteArea);

        $searchData = new SearchData([], []);
        $listener->onProductSearchDataEvent(
            new ProductSearchDataEvent('elastic', $searchData, new Pagination())
        );

        $listener->onProductSearchResultEvent(
            new ProductSearchResultEvent('elastic', new SearchResult([], []), $searchData)
        );
    }

    public function testItSkipIfIsNotSellerArea(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $websiteArea   = \Mockery::mock(WebsiteAreaService::class);
        $websiteArea->shouldReceive('isSellerArea')->twice()->withNoArgs()->andReturnFalse();

        $listener = new SellerInventoryHasStockFilterListener($entityManager, $websiteArea);

        $searchData = new SearchData([], []);
        $listener->onProductSearchDataEvent(
            new ProductSearchDataEvent(DoctrineProductSearchDriver::class, $searchData, new Pagination())
        );

        $listener->onProductSearchResultEvent(
            new ProductSearchResultEvent(DoctrineProductSearchDriver::class, new SearchResult([], []), $searchData)
        );
    }

    public function testItEnableFilterBeforeSearchingProducts(): void
    {
        $filterCollection = \Mockery::mock(FilterCollection::class);
        $filterCollection->shouldReceive('enable')->once()->with('inventoryHasStock')->andReturn();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getFilters')->once()->withNoArgs()->andReturn($filterCollection);

        $websiteArea   = \Mockery::mock(WebsiteAreaService::class);
        $websiteArea->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerInventoryHasStockFilterListener($entityManager, $websiteArea);

        $listener->onProductSearchDataEvent(
            new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination())
        );
    }

    public function testItDisableFilterAfterSearchingProducts(): void
    {
        $filterCollection = \Mockery::mock(FilterCollection::class);
        $filterCollection->shouldReceive('disable')->once()->with('inventoryHasStock')->andReturn();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('getFilters')->once()->withNoArgs()->andReturn($filterCollection);

        $websiteArea   = \Mockery::mock(WebsiteAreaService::class);
        $websiteArea->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerInventoryHasStockFilterListener($entityManager, $websiteArea);
        $searchData = new SearchData([], []);
        $listener->onProductSearchResultEvent(
            new ProductSearchResultEvent(DoctrineProductSearchDriver::class, new SearchResult([], []), $searchData)
        );
    }
}
