<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\OrderItem;
use App\Serializer\Normalizer\AdminOrderItemNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class AdminOrderItemNormalizerTest extends MockeryTestCase
{
    private $orderItem;

    protected function setUp(): void
    {
        $this->orderItem = \Mockery::mock(OrderItem::class);
    }

    protected function tearDown(): void
    {
        $this->orderItem = null;
    }

    public function testItDoesNotSupportNormalizationIfAreaIsNotAdmin()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isAdminArea')
                    ->once()
                    ->withNoArgs()
                    ->andReturnFalse();

        $normalizer = new AdminOrderItemNormalizer(
            $areaService,
            \Mockery::mock(ObjectNormalizer::class)
        );

        self::assertFalse($normalizer->supportsNormalization($this->orderItem, 'json'));
    }

    public function testItDoesNotSupportNormalizationIfDataIsNotOrderItem()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isAdminArea')
                    ->once()
                    ->withNoArgs()
                    ->andReturnTrue();

        $normalizer = new AdminOrderItemNormalizer(
            $areaService,
            \Mockery::mock(ObjectNormalizer::class)
        );

        self::assertFalse($normalizer->supportsNormalization(new \stdClass(), 'json'));
    }

    public function testItNormalizeOrderItem()
    {
        $this->orderItem->shouldReceive('isSent')->once()->withNoArgs()->andReturnTrue();

        $format  = 'json';
        $context = [];

        $areaService = \Mockery::mock(WebsiteAreaService::class);

        $objectNormalizer = \Mockery::mock(ObjectNormalizer::class);
        $objectNormalizer->shouldReceive('normalize')->once()->with($this->orderItem, $format, $context)->andReturn([]);

        $normalizer = new AdminOrderItemNormalizer($areaService, $objectNormalizer);

        $result = $normalizer->normalize($this->orderItem, $format, $context);
        self::assertArrayHasKey('sent', $result);
        self::assertTrue($result['sent']);
    }
}
