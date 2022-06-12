<?php

namespace App\Tests\Unit\Service\Utils;

use App\Dictionary\WebsiteAreaDictionary;
use App\Service\Utils\WebsiteAreaService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class WebsiteAreaServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|RequestStack|null $requestStackMock;

    protected Request|LegacyMockInterface|MockInterface|null $requestMock;

    protected LegacyMockInterface|MockInterface|ParameterBag|null $attributeMock;

    protected ?WebsiteAreaService $websiteAreaService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestStackMock = Mockery::mock(RequestStack::class);
        $this->requestMock      = Mockery::mock(Request::class);
        $this->attributeMock    = Mockery::mock(ParameterBag::class);

        $this->requestMock->attributes = $this->attributeMock;

        $this->websiteAreaService = new WebsiteAreaService($this->requestStackMock);
    }

    public function testItReturnNullIfIsNotInHttpRequestContext(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturnNull();

        self::assertNull($this->websiteAreaService->getArea());
    }

    public function testItCanGetWebsiteArea(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturn(WebsiteAreaDictionary::AREA_SELLER);

        $result = $this->websiteAreaService->getArea();

        self::assertEquals(WebsiteAreaDictionary::AREA_SELLER, $result);
    }

    public function testItCanNotGetWebsiteArea(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturnNull();

        self::assertNull($this->websiteAreaService->getArea());
    }

    public function testItCanCheckWebsiteAreaWhenWebsiteAreaIsInvalid(): void
    {
        self::assertFalse($this->websiteAreaService->isArea('invalid'));
    }

    public function testItCanCheckWebsiteAreaWhenWebsiteAreaIsSeller(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturn(WebsiteAreaDictionary::AREA_SELLER);

        self::assertTrue($this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_SELLER));
    }

    public function testItCanCheckWebsiteAreaWhenWebsiteAreaIsNotSeller(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturn(WebsiteAreaDictionary::AREA_CUSTOMER);

        self::assertFalse($this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_SELLER));
    }

    public function testItCanCheckRequestIsMadeInAdminArea(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturn(WebsiteAreaDictionary::AREA_ADMIN);

        self::assertTrue($this->websiteAreaService->isAdminArea());
    }

    public function testItCanCheckRequestIsMadeInSellerArea(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturn(WebsiteAreaDictionary::AREA_SELLER);

        self::assertTrue($this->websiteAreaService->isSellerArea());
    }

    public function testItCanCheckRequestIsMadeInCustomerArea(): void
    {
        $this->requestStackMock->shouldReceive('getCurrentRequest')
                               ->once()
                               ->withNoArgs()
                               ->andReturn($this->requestMock);

        $this->attributeMock->shouldReceive('get')
                            ->once()
                            ->with('website_area')
                            ->andReturn(WebsiteAreaDictionary::AREA_CUSTOMER);

        self::assertTrue($this->websiteAreaService->isCustomerArea());
    }
}
