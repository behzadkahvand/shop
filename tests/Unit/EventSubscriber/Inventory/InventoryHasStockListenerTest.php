<?php

namespace App\Tests\Unit\EventSubscriber\Inventory;

use App\EventSubscriber\Inventory\InventoryHasStockListener;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InventoryHasStockListenerTest extends MockeryTestCase
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

    protected InventoryHasStockListener $inventoryHasStockListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                   = Mockery::mock(EntityManagerInterface::class);
        $this->websiteAreaMock      = Mockery::mock(WebsiteAreaService::class);
        $this->requestEventMock     = Mockery::mock(RequestEvent::class);
        $this->filterCollectionMock = Mockery::mock(FilterCollection::class);

        $this->inventoryHasStockListener = new InventoryHasStockListener($this->em, $this->websiteAreaMock);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->inventoryHasStockListener::getSubscribedEvents();

        self::assertEquals([KernelEvents::REQUEST => ['onKernelRequest', 1]], $result);
    }

    public function testItDoNothingWhenRequestIsNotMaster()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(false);

        self::assertNull($this->inventoryHasStockListener->onKernelRequest($this->requestEventMock));
    }

    public function testItDoNothingWhenWebsiteAreaIsNotCustomer()
    {
        $this->requestEventMock->shouldReceive('isMainRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn(true);

        $this->websiteAreaMock->shouldReceive('isCustomerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnFalse();

        self::assertNull($this->inventoryHasStockListener->onKernelRequest($this->requestEventMock));
    }

    public function testItCanSetInventoryIsActiveFilter()
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
                                   ->with('inventoryHasStock');

        self::assertNull($this->inventoryHasStockListener->onKernelRequest($this->requestEventMock));
    }
}
