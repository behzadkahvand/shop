<?php

namespace App\Tests\Unit\EventSubscriber\Inventory;

use App\Dictionary\WebsiteAreaDictionary;
use App\EventSubscriber\Inventory\InventoryIsActiveListener;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InventoryIsActiveListenerTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|RequestEvent
     */
    protected $requestEventMock;

    /**
     * @var FilterCollection|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $filterCollectionMock;

    protected InventoryIsActiveListener $inventoryIsActiveSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                   = Mockery::mock(EntityManagerInterface::class);
        $this->websiteAreaMock      = Mockery::mock(WebsiteAreaService::class);
        $this->requestEventMock     = Mockery::mock(RequestEvent::class);
        $this->filterCollectionMock = Mockery::mock(FilterCollection::class);

        $this->inventoryIsActiveSubscriber = new InventoryIsActiveListener($this->em, $this->websiteAreaMock);
    }

    protected function tearDown(): void
    {
        $this->em = null;
        $this->websiteAreaMock = null;
        $this->requestEventMock = null;
        $this->filterCollectionMock = null;

        unset($this->inventoryIsActiveSubscriber);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->inventoryIsActiveSubscriber::getSubscribedEvents();

        self::assertEquals([KernelEvents::REQUEST => ['onKernelRequest', 1]], $result);
    }

    public function testItDoNothingWhenRequestIsNotMaster()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(false);

        self::assertNull($this->inventoryIsActiveSubscriber->onKernelRequest($this->requestEventMock));
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(true);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnFalse();

        self::assertNull($this->inventoryIsActiveSubscriber->onKernelRequest($this->requestEventMock));
    }

    public function testItCanSetInventoryIsActiveFilter()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(true);

        $this->websiteAreaMock->shouldReceive('isArea')
                              ->once()
                              ->with(WebsiteAreaDictionary::AREA_CUSTOMER)
                              ->andReturnTrue();

        $this->em->shouldReceive('getFilters')
                 ->once()
                 ->withNoArgs()
                 ->andReturn($this->filterCollectionMock);

        $this->filterCollectionMock->shouldReceive('enable')
                                   ->once()
                                   ->with('inventoryIsActive');

        self::assertNull($this->inventoryIsActiveSubscriber->onKernelRequest($this->requestEventMock));
    }
}
