<?php

namespace App\Tests\Unit\EventSubscriber\Product;

use App\EventSubscriber\Product\ProductWaitingForAcceptStatusListener;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProductWaitingForAcceptStatusListenerTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $em;

    /**
     * @var WebsiteAreaService|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $websiteAreaMock;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|RequestEvent
     */
    protected $requestEventMock;

    /**
     * @var FilterCollection|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $filterCollectionMock;

    protected ProductWaitingForAcceptStatusListener $productWaitingForAcceptStatusListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                   = Mockery::mock(EntityManagerInterface::class);
        $this->websiteAreaMock      = Mockery::mock(WebsiteAreaService::class);
        $this->requestEventMock     = Mockery::mock(RequestEvent::class);
        $this->filterCollectionMock = Mockery::mock(FilterCollection::class);

        $this->productWaitingForAcceptStatusListener = new ProductWaitingForAcceptStatusListener(
            $this->em,
            $this->websiteAreaMock
        );
    }

    protected function tearDown(): void
    {
        $this->em                   = null;
        $this->websiteAreaMock      = null;
        $this->requestEventMock     = null;
        $this->filterCollectionMock = null;

        unset($this->productWaitingForAcceptStatusListener);
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->productWaitingForAcceptStatusListener::getSubscribedEvents();

        self::assertEquals([KernelEvents::REQUEST => 'onKernelRequest'], $result);
    }

    public function testItDoNothingWhenRequestIsNotMaster(): void
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(false);

        $this->productWaitingForAcceptStatusListener->onKernelRequest($this->requestEventMock);
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer(): void
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(true);

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnFalse();

        $this->productWaitingForAcceptStatusListener->onKernelRequest($this->requestEventMock);
    }

    public function testItCanSetInventoryIsActiveFilter(): void
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(true);

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->em->shouldReceive('getFilters')
                 ->once()
                 ->withNoArgs()
                 ->andReturn($this->filterCollectionMock);

        $this->filterCollectionMock->shouldReceive('enable')
                                   ->once()
                                   ->with('productWaitingForAcceptStatus');

        $this->productWaitingForAcceptStatusListener->onKernelRequest($this->requestEventMock);
    }
}
