<?php

namespace App\Tests\Unit\EventSubscriber\OrderItem;

use App\EventSubscriber\OrderItem\SellerAreaDeletedAtOrderItemFilterListener;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SellerAreaDeletedAtOrderItemFilterListenerTest
 */
final class SellerAreaDeletedAtOrderItemFilterListenerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|RequestEvent
     */
    private $requestEventMock;

    /**
     * @var EntityManagerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $em;

    /**
     * @var FilterCollection|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $filterCollectionMock;

    /**
     * @var WebsiteAreaService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $websiteAreaService;

    private SellerAreaDeletedAtOrderItemFilterListener $listener;

    protected function setUp(): void
    {
        $this->requestEventMock     = Mockery::mock(RequestEvent::class);
        $this->em                   = Mockery::mock(EntityManagerInterface::class);
        $this->filterCollectionMock = Mockery::mock(FilterCollection::class);
        $this->websiteAreaService   = Mockery::mock(WebsiteAreaService::class);
        $this->listener             = new SellerAreaDeletedAtOrderItemFilterListener(
            $this->em,
            $this->websiteAreaService
        );
    }

    protected function tearDown(): void
    {
        $this->em                   = null;
        $this->websiteAreaService   = null;
        $this->requestEventMock     = null;
        $this->filterCollectionMock = null;

        unset($this->listener);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = SellerAreaDeletedAtOrderItemFilterListener::getSubscribedEvents();

        self::assertEquals([KernelEvents::REQUEST => ['onKernelRequest', 1]], $result);
    }

    public function testItDoNothingWhenRequestIsNotMaster()
    {
        $this->websiteAreaService->shouldNotReceive('isSellerArea');

        $this->em->shouldNotReceive('getFilters');

        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturnFalse();

        $this->listener->onKernelRequest($this->requestEventMock);
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturnTrue();

        $this->websiteAreaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnFalse();

        $this->em->shouldNotReceive('getFilters');

        $this->listener->onKernelRequest($this->requestEventMock);
    }

    public function testItCanSetInventoryIsActiveFilter()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(true);

        $this->websiteAreaService->shouldReceive('isSellerArea')->once()->with()->andReturnTrue();

        $this->em->shouldReceive('getFilters')
                 ->once()
                 ->withNoArgs()
                 ->andReturn($this->filterCollectionMock);

        $this->filterCollectionMock->shouldReceive('disable')
                                   ->once()
                                   ->with('softdeleteable');

        $this->listener->onKernelRequest($this->requestEventMock);
    }
}
