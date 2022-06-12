<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners;

use App\Entity\Customer;
use App\Events\Product\Search\ProductSearchResultEvent;
use App\Service\Product\Logs\SearchLogService;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\LogSearchSubscriber;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\SearchResult;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Security\Core\Security;

class LogSearchSubscriberTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|WebsiteAreaService|null $websiteAreaMock;

    protected LegacyMockInterface|MockInterface|Security|null $securityMock;

    protected LegacyMockInterface|SearchLogService|MockInterface|null $searchLogMock;

    protected ?LogSearchSubscriber $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock = Mockery::mock(WebsiteAreaService::class);
        $this->securityMock    = Mockery::mock(Security::class);
        $this->searchLogMock   = Mockery::mock(SearchLogService::class);

        $this->sut = new LogSearchSubscriber(
            $this->websiteAreaMock,
            $this->securityMock,
            $this->searchLogMock
        );
    }

    public function testGettingSubscribedEvents(): void
    {
        self::assertEquals(
            [ProductSearchResultEvent::class => 'onProductSearchResult'],
            $this->sut::getSubscribedEvents()
        );
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer(): void
    {
        $searchData = new SearchData(['title' => 'test'], []);
        $event      = new ProductSearchResultEvent(
            DoctrineProductSearchDriver::class,
            new SearchResult([], []),
            $searchData
        );

        $this->websiteAreaMock->expects('isCustomerArea')->withNoArgs()->andReturnFalse();

        $this->sut->onProductSearchResult($event);
    }

    public function testItDoNothingWhenFilterTitleIsNotSet(): void
    {
        $searchData = new SearchData([], []);
        $event      = new ProductSearchResultEvent(
            DoctrineProductSearchDriver::class,
            new SearchResult([], []),
            $searchData
        );

        $this->websiteAreaMock->expects('isCustomerArea')->withNoArgs()->andReturnTrue();

        $this->sut->onProductSearchResult($event);
    }

    public function testItDoNothingWhenFilterTitleIsNotString(): void
    {
        $searchData = new SearchData(['title' => 2], []);
        $event      = new ProductSearchResultEvent(
            DoctrineProductSearchDriver::class,
            new SearchResult([], []),
            $searchData
        );

        $this->websiteAreaMock->expects('isCustomerArea')->withNoArgs()->andReturnTrue();

        $this->sut->onProductSearchResult($event);
    }

    public function testItCanLogSearchTitleWhenTitleIsArrayAndMetaHasTotalItemsAndCustomerExists(): void
    {
        $searchData = new SearchData(['title' => ['test']], []);
        $event      = new ProductSearchResultEvent(
            DoctrineProductSearchDriver::class,
            new SearchResult([], ['totalItems' => 10]),
            $searchData
        );

        $customerMock = Mockery::mock(Customer::class);

        $this->websiteAreaMock->expects('isCustomerArea')->withNoArgs()->andReturnTrue();

        $this->securityMock->expects('getUser')->withNoArgs()->andReturns($customerMock);

        $customerMock->expects('getId')->withNoArgs()->andReturns(32);

        $this->searchLogMock->expects('dispatchSearchLogMsg')
                            ->with('test', 10, 32)
                            ->andReturns();

        $this->sut->onProductSearchResult($event);
    }

    public function testItCanLogSearchTitleWhenTitleIsStringAndMetaHasNotTotalItemsAndCustomerNotExists(): void
    {
        $searchData = new SearchData(['title' => 'test'], []);
        $event      = new ProductSearchResultEvent(
            DoctrineProductSearchDriver::class,
            new SearchResult([], []),
            $searchData
        );

        $this->websiteAreaMock->expects('isCustomerArea')->withNoArgs()->andReturnTrue();

        $this->securityMock->expects('getUser')->withNoArgs()->andReturnNull();

        $this->searchLogMock->expects('dispatchSearchLogMsg')
                            ->with('test', 0, null)
                            ->andReturns();

        $this->sut->onProductSearchResult($event);
    }
}
