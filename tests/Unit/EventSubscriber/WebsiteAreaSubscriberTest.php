<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Dictionary\WebsiteAreaDictionary;
use App\EventSubscriber\WebsiteAreaSubscriber;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class WebsiteAreaSubscriberTest extends MockeryTestCase
{
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|RequestEvent
     */
    protected $requestEventMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Request
     */
    protected $requestMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ParameterBag
     */
    protected $attributeMock;

    protected WebsiteAreaSubscriber $websiteAreaSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestEventMock = Mockery::mock(RequestEvent::class);
        $this->requestMock      = Mockery::mock(Request::class);
        $this->attributeMock    = Mockery::mock(ParameterBag::class);

        $this->requestMock->attributes = $this->attributeMock;

        $this->websiteAreaSubscriber = new WebsiteAreaSubscriber();
    }

    protected function tearDown(): void
    {
        $this->requestEventMock = null;
        $this->requestMock = null;
        $this->attributeMock = null;

        unset($this->websiteAreaSubscriber);
    }

    public function testItCanGetSubscribedEvents()
    {
        $result = $this->websiteAreaSubscriber::getSubscribedEvents();

        self::assertEquals([KernelEvents::REQUEST => ['onKernelRequest', 11]], $result);
    }

    public function testSetWebsiteAreaToCustomer()
    {
        $this->requestEventMock->shouldReceive('getRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->requestMock->shouldReceive('getPathInfo')
                          ->once()
                          ->withNoArgs()
                          ->andReturn("/products/search");

        $this->attributeMock->shouldReceive('set')
                          ->once()
                          ->with('website_area', WebsiteAreaDictionary::AREA_CUSTOMER)
                          ->andReturnNull();

        self::assertNull($this->websiteAreaSubscriber->onKernelRequest($this->requestEventMock));
    }

    public function testSetWebsiteAreaToAdmin()
    {
        $this->requestEventMock->shouldReceive('getRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->requestMock->shouldReceive('getPathInfo')
                          ->once()
                          ->withNoArgs()
                          ->andReturn("/admin/shipping-methods");

        $this->attributeMock->shouldReceive('set')
                            ->once()
                            ->with('website_area', WebsiteAreaDictionary::AREA_ADMIN)
                            ->andReturnNull();

        self::assertNull($this->websiteAreaSubscriber->onKernelRequest($this->requestEventMock));
    }

    public function testSetWebsiteAreaToSeller()
    {
        $this->requestEventMock->shouldReceive('getRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->requestMock->shouldReceive('getPathInfo')
                          ->once()
                          ->withNoArgs()
                          ->andReturn("/seller/products/search");

        $this->attributeMock->shouldReceive('set')
                            ->once()
                            ->with('website_area', WebsiteAreaDictionary::AREA_SELLER)
                            ->andReturnNull();

        self::assertNull($this->websiteAreaSubscriber->onKernelRequest($this->requestEventMock));
    }
}
