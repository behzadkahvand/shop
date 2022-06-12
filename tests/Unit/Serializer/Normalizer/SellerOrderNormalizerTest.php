<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderStatus;
use App\Entity\Order;
use App\Serializer\Normalizer\SellerOrderNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SellerOrderNormalizerTest extends MockeryTestCase
{
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ObjectNormalizer
     */
    protected $normalizerMock;

    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaServiceMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizerMock         = Mockery::mock(ObjectNormalizer::class);
        $this->websiteAreaServiceMock = Mockery::mock(WebsiteAreaService::class);
        $this->orderMock              = Mockery::mock(Order::class);
    }

    protected function tearDown(): void
    {
        $this->normalizerMock = null;
        $this->websiteAreaServiceMock = null;
        $this->orderMock = null;
    }

    public function testItDoesNotSupportNormalizationIfAreaIsNotSeller()
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderNormalizer->supportsNormalization($this->orderMock, 'json'));
    }

    public function testItDoesNotSupportNormalizationIfDataIsNotOrder()
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderNormalizer->supportsNormalization(new stdClass(), 'json'));
    }

    public function testItSupportsNormalization()
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertTrue($orderNormalizer->supportsNormalization($this->orderMock, 'json'));
    }

    public function testItCanNormalizeOrderData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([
                                 'status' => OrderStatus::CONFIRMED,
                                 'id'     => 1
                             ]);

        $this->orderMock->shouldReceive('getIdentifier')
                        ->once()
                        ->withNoArgs()
                        ->andReturn("12364564");

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertArrayHasKey('status', $result);
        self::assertArrayHasKey('id', $result);
        self::assertEquals(SellerOrderStatus::CONFIRMED, $result['status']);
        self::assertEquals("12364564", $result['id']);
    }

    public function testItCanNormalizeOrderDataWhenStatusDoesNotExistInData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([
                                 'id' => 1
                             ]);

        $this->orderMock->shouldReceive('getIdentifier')
                        ->once()
                        ->withNoArgs()
                        ->andReturn("12364564");

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertArrayHasKey('id', $result);
        self::assertEquals("12364564", $result['id']);
    }

    public function testItCanNormalizeOrderDataWhenIdDoesNotExistInData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([
                                 'status' => OrderStatus::WAITING_FOR_PAY
                             ]);

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertArrayHasKey('status', $result);
        self::assertEquals(SellerOrderStatus::WAITING_FOR_PAY, $result['status']);
    }

    public function testItCanNormalizeOrderDataWhenIdAndStatusDoesNotExistInData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new SellerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([]);

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertEquals([], $result);
    }
}
