<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Serializer\Normalizer\SellerProductOptionAndProductOptionValueNormalizer;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SellerProductOptionAndProductOptionValueNormalizerTest
 */
final class SellerProductOptionAndProductOptionValueNormalizerTest extends MockeryTestCase
{
    public function testItDoesNotSupportIfAreaIsNotSellerArea()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnFalse();
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        self::assertFalse($normalizer->supportsNormalization(new ProductOption()));
    }

    public function testItDoesNotSupportIfDataIsNotAnObject()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        self::assertFalse($normalizer->supportsNormalization([]));
    }

    public function testItDoesNotSupportIfDataIsNotAnInstanceofProductOptionOrProductOptionValue()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        self::assertFalse($normalizer->supportsNormalization(new \stdClass()));
    }

    public function testItSupportItSupportProductOption()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        self::assertTrue($normalizer->supportsNormalization(new ProductOption()));
    }

    public function testItSupportItSupportProductOptionValue()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $areaService->shouldReceive('isSellerArea')->once()->withNoArgs()->andReturnTrue();
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        self::assertTrue($normalizer->supportsNormalization(new ProductOptionValue()));
    }

    public function testItNormalizeProductOption()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        $productOption = \Mockery::mock(ProductOption::class);
        $productOption->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $productOption->shouldReceive('getName')->once()->withNoArgs()->andReturn('po');
        $productOption->shouldReceive('getCode')->once()->withNoArgs()->andReturn('po');
        $productOption->shouldReceive('getValues')->once()->withNoArgs()->andReturnUsing(function () {
            $productOptionValue = \Mockery::mock(ProductOptionValue::class);

            $productOptionValue->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
            $productOptionValue->shouldReceive('getCode')->once()->withNoArgs()->andReturn('pov');
            $productOptionValue->shouldReceive('getValue')->once()->withNoArgs()->andReturn('pov');
            $productOptionValue->shouldReceive('getAttributes')->once()->withNoArgs()->andReturn([]);

            return new ArrayCollection([$productOptionValue]);
        });

        $normalized = $normalizer->normalize($productOption);

        self::assertArrayHasKey('id', $normalized);
        self::assertArrayHasKey('name', $normalized);
        self::assertArrayHasKey('code', $normalized);
        self::assertArrayHasKey('values', $normalized);
        self::assertIsArray($normalized['values']);
        foreach ($normalized['values'] as $value) {
            self::assertArrayHasKey('id', $value);
            self::assertArrayHasKey('code', $value);
            self::assertArrayHasKey('value', $value);
            self::assertArrayHasKey('attributes', $value);
        }
    }

    public function testItNormalizeProductOptionValue()
    {
        $areaService = \Mockery::mock(WebsiteAreaService::class);
        $normalizer = new SellerProductOptionAndProductOptionValueNormalizer($areaService);

        $productOptionValue = \Mockery::mock(ProductOptionValue::class);

        $productOptionValue->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $productOptionValue->shouldReceive('getCode')->once()->withNoArgs()->andReturn('pov');
        $productOptionValue->shouldReceive('getValue')->once()->withNoArgs()->andReturn('pov');
        $productOptionValue->shouldReceive('getAttributes')->once()->withNoArgs()->andReturn([]);
        $productOptionValue->shouldReceive('getOption')->once()->withNoArgs()->andReturnUsing(function () {
            $productOption = \Mockery::mock(ProductOption::class);
            $productOption->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
            $productOption->shouldReceive('getName')->once()->withNoArgs()->andReturn('po');
            $productOption->shouldReceive('getCode')->once()->withNoArgs()->andReturn('po');

            return $productOption;
        });

        $normalized = $normalizer->normalize($productOptionValue);

        self::assertArrayHasKey('id', $normalized);
        self::assertArrayHasKey('code', $normalized);
        self::assertArrayHasKey('value', $normalized);
        self::assertArrayHasKey('attributes', $normalized);
        self::assertArrayHasKey('option', $normalized);
        self::assertArrayHasKey('id', $normalized['option']);
        self::assertArrayHasKey('name', $normalized['option']);
        self::assertArrayHasKey('code', $normalized['option']);
    }
}
