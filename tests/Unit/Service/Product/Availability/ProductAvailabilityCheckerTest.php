<?php

namespace App\Tests\Unit\Service\Product\Availability;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Product\Availability\ProductAvailabilityByInventoryCheckerInterface;
use App\Service\Product\Availability\ProductAvailabilityChecker;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ProductAvailabilityCheckerTest
 */
final class ProductAvailabilityCheckerTest extends MockeryTestCase
{
    public function testItCheckProductIsAvailable(): void
    {
        $checker = new ProductAvailabilityChecker([]);

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(ProductStatusDictionary::DRAFT);
        self::assertTrue($checker->isAvailable($product));

        $product->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(ProductStatusDictionary::UNAVAILABLE);
        self::assertFalse($checker->isAvailable($product));
    }

    public function testItCheckProductShouldBeAvailable(): void
    {
        $inventory = \Mockery::mock(Inventory::class);

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getProductVariants')->twice()->withNoArgs()->andReturnUsing(
            function () use ($inventory) {
                $variant = \Mockery::mock(ProductVariant::class);
                $variant->shouldReceive('getInventories')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$inventory]));

                return new ArrayCollection([$variant]);
            }
        );

        $availabilityChecker = \Mockery::mock(ProductAvailabilityByInventoryCheckerInterface::class);
        $availabilityChecker->shouldReceive('productShouldBeUnavailable')
                            ->once()
                            ->with($inventory)
                            ->andReturnTrue();

        $checker = new ProductAvailabilityChecker([$availabilityChecker]);

        self::assertFalse($checker->shouldBeAvailable($product));

        $availabilityChecker = \Mockery::mock(ProductAvailabilityByInventoryCheckerInterface::class);
        $availabilityChecker->shouldReceive('productShouldBeUnavailable')
                            ->once()
                            ->with($inventory)
                            ->andReturnFalse();

        $checker = new ProductAvailabilityChecker([$availabilityChecker]);

        self::assertTrue($checker->shouldBeAvailable($product));
    }

    public function testItCheckProductShouldBeUnavailable(): void
    {
        $inventory = \Mockery::mock(Inventory::class);

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getProductVariants')->twice()->withNoArgs()->andReturnUsing(
            function () use ($inventory) {
                $variant = \Mockery::mock(ProductVariant::class);
                $variant->shouldReceive('getInventories')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$inventory]));

                return new ArrayCollection([$variant]);
            }
        );

        $availabilityChecker = \Mockery::mock(ProductAvailabilityByInventoryCheckerInterface::class);
        $availabilityChecker->shouldReceive('productShouldBeUnavailable')
                            ->once()
                            ->with($inventory)
                            ->andReturnFalse();

        $checker = new ProductAvailabilityChecker([$availabilityChecker]);

        self::assertFalse($checker->shouldBeUnavailable($product));

        $availabilityChecker = \Mockery::mock(ProductAvailabilityByInventoryCheckerInterface::class);
        $availabilityChecker->shouldReceive('productShouldBeUnavailable')
                            ->once()
                            ->with($inventory)
                            ->andReturnTrue();

        $checker = new ProductAvailabilityChecker([$availabilityChecker]);

        self::assertTrue($checker->shouldBeUnavailable($product));
    }

    public function testItCheckInventoryIsEligibleToChangeProductAvailability(): void
    {
        $product = \Mockery::mock(Product::class);
        $product->shouldReceive(['getStatus' => ProductStatusDictionary::CONFIRMED])->twice()->withNoArgs();

        $inventory = \Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getVariant->getProduct')->times(4)->withNoArgs()->andReturn($product);

        $availabilityChecker = \Mockery::mock(ProductAvailabilityByInventoryCheckerInterface::class);
        $availabilityChecker->shouldReceive('productShouldBeUnavailable')->once()->with($inventory)->andReturnTrue();

        $checker = new ProductAvailabilityChecker([$availabilityChecker]);

        self::assertTrue($checker->inventoryIsEligibleToChangeProductAvailability($inventory));

        $availabilityChecker->shouldReceive('productShouldBeUnavailable')->once()->with($inventory)->andReturnFalse();
        self::assertFalse($checker->inventoryIsEligibleToChangeProductAvailability($inventory));

        $product->shouldReceive(['getStatus' => ProductStatusDictionary::UNAVAILABLE])->twice()->withNoArgs();
        $availabilityChecker->shouldReceive('productShouldBeAvailable')->once()->with($inventory)->andReturnFalse();

        self::assertFalse($checker->inventoryIsEligibleToChangeProductAvailability($inventory));

        $availabilityChecker->shouldReceive('productShouldBeAvailable')->once()->with($inventory)->andReturnTrue();

        self::assertTrue($checker->inventoryIsEligibleToChangeProductAvailability($inventory));
    }
}
