<?php

namespace App\Tests\Unit\Serializer\Normalizer;

use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use App\Serializer\Normalizer\ProductVariantNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class ProductVariantNormalizerTest
 */
final class ProductVariantNormalizerTest extends MockeryTestCase
{
    public function testItSupportInstancesOfProductVariantEntity(): void
    {
        $normalizer = new ProductVariantNormalizer(\Mockery::mock(ObjectNormalizer::class));
        self::assertTrue($normalizer->supportsNormalization(\Mockery::mock(ProductVariant::class), null));
        self::assertFalse($normalizer->supportsNormalization(new \stdClass(), null));
    }

    public function testItNormalizeProductVariant(): void
    {
        $productVariant = \Mockery::mock(ProductVariant::class);
        $productVariant->shouldReceive('getColor')->once()->withNoArgs()->andReturnUsing(function () {
            $option = \Mockery::mock(ProductOption::class);
            $option->shouldReceive('getName')->once()->withNoArgs()->andReturn('color');
            $productOptionValue = \Mockery::mock(ProductOptionValue::class);
            $productOptionValue->shouldReceive([
                'getCode' => 'red',
                'getValue' => 'قرمز',
                'getAttributes' => ['hex' => '#FFFFFF'],
                'getOption' => $option,
            ])->once()->withNoArgs();

            return $productOptionValue;
        });
        $productVariant->shouldReceive('getGuaranty')->once()->withNoArgs()->andReturnUsing(function () {
            $option = \Mockery::mock(ProductOption::class);
            $option->shouldReceive('getName')->once()->withNoArgs()->andReturn('guarantee');
            $productOptionValue = \Mockery::mock(ProductOptionValue::class);
            $productOptionValue->shouldReceive([
                'getCode' => 'physical',
                'getValue' => 'سلامت فیزیکی',
                'getAttributes' => [],
                'getOption' => $option,
            ])->once()->withNoArgs();

            return $productOptionValue;
        });
        $productVariant->shouldReceive('getOtherOption')->once()->withNoArgs()->andReturnUsing(function () {
            $option = \Mockery::mock(ProductOption::class);
            $option->shouldReceive('getName')->once()->withNoArgs()->andReturn('size');
            $productOptionValue = \Mockery::mock(ProductOptionValue::class);
            $productOptionValue->shouldReceive([
                'getCode' => 'large',
                'getValue' => 'بزرگ',
                'getAttributes' => [],
                'getOption' => $option,
            ])->once()->withNoArgs();

            return $productOptionValue;
        });

        $objectNormalizer = \Mockery::mock(ObjectNormalizer::class);
        $objectNormalizer->shouldReceive('normalize')
                         ->once()
                         ->with($productVariant, null, [])
                         ->andReturn([]);

        $normalizer  = new ProductVariantNormalizer($objectNormalizer);

        $result = $normalizer->normalize($productVariant);
        self::assertArrayHasKey('options', $result);

        self::assertArrayHasKey('color', $result['options']);
        self::assertArrayHasKey('code', $result['options']['color']);
        self::assertArrayHasKey('value', $result['options']['color']);
        self::assertArrayHasKey('attributes', $result['options']['color']);
        self::assertArrayHasKey('hex', $result['options']['color']['attributes']);
        self::assertArrayHasKey('option', $result['options']['color']);
        self::assertArrayHasKey('name', $result['options']['color']['option']);

        self::assertArrayHasKey('guarantee', $result['options']);
        self::assertArrayHasKey('code', $result['options']['guarantee']);
        self::assertArrayHasKey('value', $result['options']['guarantee']);
        self::assertArrayHasKey('attributes', $result['options']['guarantee']);
        self::assertArrayHasKey('option', $result['options']['guarantee']);
        self::assertArrayHasKey('name', $result['options']['guarantee']['option']);

        self::assertArrayHasKey('otherOptions', $result['options']);
        self::assertIsArray($result['options']['otherOptions']);

        self::assertArrayHasKey('code', $result['options']['otherOptions']);
        self::assertArrayHasKey('value', $result['options']['otherOptions']);
        self::assertArrayHasKey('attributes', $result['options']['otherOptions']);
        self::assertArrayHasKey('option', $result['options']['otherOptions']);
        self::assertArrayHasKey('name', $result['options']['otherOptions']['option']);
    }

    public function testItNormalizeProductVariantWithNullOptions(): void
    {
        $productVariant = \Mockery::mock(ProductVariant::class);
        $productVariant->shouldReceive('getColor')->once()->withNoArgs()->andReturnNull();
        $productVariant->shouldReceive('getGuaranty')->once()->withNoArgs()->andReturnNull();
        $productVariant->shouldReceive('getOtherOption')->once()->withNoArgs()->andReturnNull();

        $objectNormalizer = \Mockery::mock(ObjectNormalizer::class);
        $objectNormalizer->shouldReceive('normalize')
                         ->once()
                         ->with($productVariant, null, [])
                         ->andReturn([]);

        $normalizer  = new ProductVariantNormalizer($objectNormalizer);

        $result = $normalizer->normalize($productVariant);
        self::assertArrayHasKey('options', $result);
        self::assertArrayHasKey('color', $result['options']);
        self::assertNull($result['options']['color']);
        self::assertArrayHasKey('guarantee', $result['options']);
        self::assertNull($result['options']['guarantee']);
        self::assertArrayHasKey('otherOptions', $result['options']);
        self::assertNull($result['options']['otherOptions']);
    }
}
