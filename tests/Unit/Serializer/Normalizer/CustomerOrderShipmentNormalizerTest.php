<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Dictionary\CustomerOrderShipmentStatus;
use App\Dictionary\OrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Serializer\Normalizer\CustomerOrderShipmentNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerOrderShipmentNormalizerTest extends MockeryTestCase
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
     * @var OrderShipment|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderShipmentMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizerMock         = Mockery::mock(ObjectNormalizer::class);
        $this->websiteAreaServiceMock = Mockery::mock(WebsiteAreaService::class);
        $this->orderShipmentMock      = Mockery::mock(OrderShipment::class);
    }

    protected function tearDown(): void
    {
        $this->normalizerMock = null;
        $this->websiteAreaServiceMock = null;
        $this->orderShipmentMock = null;
    }

    public function testItDoesNotSupportNormalizationIfAreaIsNotCustomer()
    {
        $this->websiteAreaServiceMock->shouldReceive('isCustomerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

        $orderShipmentNormalizer = new CustomerOrderShipmentNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderShipmentNormalizer->supportsNormalization($this->orderShipmentMock, 'json'));
    }

    public function testItDoesNotSupportNormalizationIfDataIsNotOrderShipment()
    {
        $this->websiteAreaServiceMock->shouldReceive('isCustomerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderShipmentNormalizer = new CustomerOrderShipmentNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderShipmentNormalizer->supportsNormalization(new stdClass(), 'json'));
    }

    public function testItSupportsNormalization()
    {
        $this->websiteAreaServiceMock->shouldReceive('isCustomerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderShipmentNormalizer = new CustomerOrderShipmentNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertTrue($orderShipmentNormalizer->supportsNormalization($this->orderShipmentMock, 'json'));
    }

    public function testItCanNormalizeOrderData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new CustomerOrderShipmentNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderShipmentMock, $format, $context)
                             ->andReturn([
                                 'status' => OrderShipmentStatus::WAITING_FOR_SUPPLY
                             ]);

        $result = $orderNormalizer->normalize($this->orderShipmentMock, $format, $context);

        self::assertArrayHasKey('status', $result);
        self::assertEquals(CustomerOrderShipmentStatus::WAITING_FOR_SUPPLY, $result['status']);
    }
}
