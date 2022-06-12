<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Dictionary\CustomerOrderStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Serializer\Normalizer\CustomerOrderNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerOrderNormalizerTest extends MockeryTestCase
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

    public function testItDoesNotSupportNormalizationIfAreaIsNotCustomer()
    {
        $this->websiteAreaServiceMock->shouldReceive('isCustomerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

        $orderNormalizer = new CustomerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderNormalizer->supportsNormalization($this->orderMock, 'json'));
    }

    public function testItDoesNotSupportNormalizationIfDataIsNotOrder()
    {
        $this->websiteAreaServiceMock->shouldReceive('isCustomerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderNormalizer = new CustomerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderNormalizer->supportsNormalization(new stdClass(), 'json'));
    }

    public function testItSupportsNormalization()
    {
        $this->websiteAreaServiceMock->shouldReceive('isCustomerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderNormalizer = new CustomerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertTrue($orderNormalizer->supportsNormalization($this->orderMock, 'json'));
    }

    public function testItCanNormalizeOrderData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new CustomerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([
                                 'status' => OrderStatus::CONFIRMED
                             ]);

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertArrayHasKey('status', $result);
        self::assertEquals(CustomerOrderStatus::CONFIRMED, $result['status']);
    }

    public function testItReplaceOrderIdWithItsIdentifier()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new CustomerOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([
                                 'id'     => 1,
                                 'status' => OrderStatus::CONFIRMED,
                             ]);

        $identifier = random_int(1, 10);
        $this->orderMock->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn($identifier);

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertArrayHasKey('status', $result);
        self::assertArrayHasKey('id', $result);
        self::assertEquals(CustomerOrderStatus::CONFIRMED, $result['status']);
        self::assertEquals($identifier, $result['id']);
    }
}
