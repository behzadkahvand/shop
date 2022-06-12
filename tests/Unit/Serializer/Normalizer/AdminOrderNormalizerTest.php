<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Entity\OrderCancelReason;
use App\Entity\OrderCancelReasonOrder;
use App\Repository\OrderCancelReasonOrderRepository;
use App\Serializer\Normalizer\AdminOrderNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AdminOrderNormalizerTest extends MockeryTestCase
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
     * @var OrderCancelReasonOrderRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $repository;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizerMock         = Mockery::mock(ObjectNormalizer::class);
        $this->websiteAreaServiceMock = Mockery::mock(WebsiteAreaService::class);
        $this->repository             = Mockery::mock(OrderCancelReasonOrderRepository::class);
        $this->orderMock              = Mockery::mock(Order::class);
    }

    protected function tearDown(): void
    {
        $this->normalizerMock = null;
        $this->websiteAreaServiceMock = null;
        $this->repository = null;
        $this->orderMock = null;
    }

    public function testItDoesNotSupportNormalizationIfAreaIsNotSeller()
    {
        $this->websiteAreaServiceMock->shouldReceive('isAdminArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnFalse();

        $orderNormalizer = new AdminOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock,
            $this->repository
        );

        self::assertFalse($orderNormalizer->supportsNormalization($this->orderMock, 'json'));
    }

    public function testItDoesNotSupportNormalizationIfDataIsNotOrder()
    {
        $this->websiteAreaServiceMock->shouldReceive('isAdminArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderNormalizer = new AdminOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock,
            $this->repository
        );

        self::assertFalse($orderNormalizer->supportsNormalization(new stdClass(), 'json'));
    }

    public function testItSupportsNormalization()
    {
        $this->websiteAreaServiceMock->shouldReceive('isAdminArea')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturnTrue();

        $orderNormalizer = new AdminOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock,
            $this->repository
        );

        self::assertTrue($orderNormalizer->supportsNormalization($this->orderMock, 'json'));
    }

    public function testItCanNormalizeOrderData()
    {
        $format  = 'json';
        $context = [];

        $orderNormalizer = new AdminOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock,
            $this->repository
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

        $orderNormalizer = new AdminOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock,
            $this->repository
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn([]);

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertEquals([], $result);
    }

    public function testItCanAddCancelReasonToPayload(): void
    {
        $format  = 'json';
        $context = [];

        $reason = Mockery::mock(OrderCancelReason::class);
        $reason->shouldReceive(['getReason' => 'no reason'])->once()->withNoArgs();

        $orderCancelReasonOrder = Mockery::mock(OrderCancelReasonOrder::class);
        $orderCancelReasonOrder->shouldReceive('getCancelReason')->once()->withNoArgs()->andReturn($reason);

        $orderNormalizer = new AdminOrderNormalizer(
            $this->websiteAreaServiceMock,
            $this->normalizerMock,
            $this->repository
        );

        $this->normalizerMock->shouldReceive('normalize')
                             ->once()
                             ->with($this->orderMock, $format, $context)
                             ->andReturn(['status' => OrderStatus::CANCELED]);

        $this->repository->shouldReceive('findOneBy')
                         ->once()
                         ->with(['order' => $this->orderMock])
                         ->andReturn($orderCancelReasonOrder);

        $result = $orderNormalizer->normalize($this->orderMock, $format, $context);

        self::assertEquals(['status' => OrderStatus::CANCELED, 'cancelReason' => 'no reason'], $result);
    }
}
