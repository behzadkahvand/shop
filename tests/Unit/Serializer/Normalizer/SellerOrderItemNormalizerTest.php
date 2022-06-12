<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Dictionary\SellerOrderItemStatusMappingDictionary;
use App\Dictionary\SellerOrderItemStatus;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Serializer\Normalizer\SellerOrderItemNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SellerOrderItemNormalizerTest extends MockeryTestCase
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
    protected $sellerOrderItemMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizerMock         = Mockery::mock(ObjectNormalizer::class);
        $this->websiteAreaServiceMock = Mockery::mock(WebsiteAreaService::class);
        $this->sellerOrderItemMock    = Mockery::mock(SellerOrderItem::class);
    }

    protected function tearDown(): void
    {
        $this->normalizerMock         = null;
        $this->websiteAreaServiceMock = null;
        $this->sellerOrderItemMock    = null;
    }

    public function testItDoesNotSupportNormalizationIfAreaIsNotCustomer()
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

        $orderShipmentNormalizer = new SellerOrderItemNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderShipmentNormalizer->supportsNormalization($this->sellerOrderItemMock, 'json'));
    }

    public function testItDoesNotSupportNormalizationIfDataIsNotOrderShipment()
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderShipmentNormalizer = new SellerOrderItemNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertFalse($orderShipmentNormalizer->supportsNormalization(new stdClass(), 'json'));
    }

    public function testItSupportsNormalization()
    {
        $this->websiteAreaServiceMock->shouldReceive('isSellerArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderShipmentNormalizer = new SellerOrderItemNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        self::assertTrue($orderShipmentNormalizer->supportsNormalization($this->sellerOrderItemMock, 'json'));
    }

    public function testItCanNormalizeOrderData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new SellerOrderItemNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->sellerOrderItemMock, $format, $context)
                             ->andReturn([
                                 'status' => SellerOrderItemStatus::WAITING_FOR_SEND,
                             ]);

        $result = $orderNormalizer->normalize($this->sellerOrderItemMock, $format, $context);

        self::assertArrayHasKey('status', $result);
        self::assertEquals(SellerOrderItemStatusMappingDictionary::WAITING_FOR_SEND, $result['status']);
    }
}
