<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\SellerSearchByProductIdFilterListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerSearchByProductIdFilterListenerTest
 */
final class SellerSearchByProductIdFilterListenerTest extends MockeryTestCase
{
    public function testGettingSubscribedEvents(): void
    {
        self::assertEquals([
            ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1],
        ], SellerSearchByProductIdFilterListener::getSubscribedEvents());
    }
    public function testItDoNothingIfDriverIsNotDoctrine(): void
    {
        $event = new ProductSearchDataEvent('dummyDriver', new SearchData([], []), new Pagination());

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldNotReceive('isSellerArea');

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);
    }

    public function testItDoNothingIfIsNotSellerArea(): void
    {
        $data = new SearchData([], []);

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $data,
            new Pagination()
        );

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnFalse();

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);

        self::assertSame($data, $event->getData());
    }

    public function testItDoNothingIfTitleIsNotSet(): void
    {
        $data = new DoctrineSearchData([], []);

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $data,
            new Pagination()
        );

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);

        self::assertSame($data, $event->getData());
    }

    public function testItDoNothingIfProductIdIsNotGiven(): void
    {
        $data = new DoctrineSearchData([], [], null, 'foo');

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $data,
            new Pagination()
        );

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);

        self::assertSame($data, $event->getData());
    }

    public function testItDoNothingIfFormattedProductIdIsWrong(): void
    {
        $data = new DoctrineSearchData([], [], null, 'tpi_1');

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $data,
            new Pagination()
        );

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);

        self::assertSame($data, $event->getData());
    }

    public function testItChangeFiltersIfNumericProductIdGiven(): void
    {
        $data = new DoctrineSearchData([], [], null, 1);

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $data,
            new Pagination()
        );

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);

        self::assertInstanceOf(DoctrineSearchData::class, $event->getData());
        self::assertNull($event->getData()->getTitle());
        self::assertEquals(['id' => 1], $event->getData()->getFilters());
    }

    public function testItChangeFiltersIfFormattedProductIdGiven(): void
    {
        $data = new DoctrineSearchData([], [], null, "tpi-1");

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            $data,
            new Pagination()
        );

        $websiteAreaService = \Mockery::mock(WebsiteAreaService::class);
        $websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();

        $listener = new SellerSearchByProductIdFilterListener($websiteAreaService);
        $listener->onProductSearchDataEvent($event);

        self::assertInstanceOf(DoctrineSearchData::class, $event->getData());
        self::assertNull($event->getData()->getTitle());
        self::assertEquals(['id' => 1], $event->getData()->getFilters());
    }
}
