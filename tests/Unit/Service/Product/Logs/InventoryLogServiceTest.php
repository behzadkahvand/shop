<?php

namespace App\Tests\Unit\Service\Product\Logs;

use App\DTO\InventoryLogData;
use App\DTO\InventoryPriceHistoryData;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use App\Entity\Seller;
use App\Messaging\Messages\Command\Product\LogInventory;
use App\Messaging\Messages\Command\Product\LogInventoryPriceChange;
use App\Service\Product\Logs\InventoryLogService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class InventoryLogServiceTest extends BaseUnitTestCase
{
    protected ?MessageBusInterface $messageBusMock;

    protected ?InventoryLogService $inventoryLogService;

    protected ?Inventory $inventoryEntityMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBusMock = Mockery::mock(MessageBusInterface::class);

        $this->inventoryLogService = new InventoryLogService($this->messageBusMock);
    }

    public function testHasInventoryPriceChangedWhenOnlyPriceChangedButOldAndNewValuesAreEqual(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('price')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->once()
             ->with('price')
             ->andReturn(100);

        $args->shouldReceive('getNewValue')
             ->once()
             ->with('price')
             ->andReturn(100);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('finalPrice')
             ->andReturn(false);

        $result = $this->inventoryLogService->hasInventoryPriceChanged($args);

        $this->assertFalse($result);
    }

    public function testHasInventoryPriceChangedWhenOnlyPriceChangedAndOldAndNewValuesAreNotEqual(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('price')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->once()
             ->with('price')
             ->andReturn(100);

        $args->shouldReceive('getNewValue')
             ->once()
             ->with('price')
             ->andReturn(110);

        $args->shouldReceive('hasChangedField')
             ->with('finalPrice')
             ->andReturn(false);

        $result = $this->inventoryLogService->hasInventoryPriceChanged($args);

        $this->assertTrue($result);
    }

    public function testHasInventoryPriceChangedWhenOnlyFinalPriceChangedButOldAndNewValuesAreEqual(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('finalPrice')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->once()
             ->with('finalPrice')
             ->andReturn(200);

        $args->shouldReceive('getNewValue')
             ->once()
             ->with('finalPrice')
             ->andReturn(200);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('price')
             ->andReturn(false);

        $result = $this->inventoryLogService->hasInventoryPriceChanged($args);

        $this->assertFalse($result);
    }

    public function testHasInventoryPriceChangedWhenOnlyFinalPriceChangedAndOldAndNewValuesAreNotEqual(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('finalPrice')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->once()
             ->with('finalPrice')
             ->andReturn(200);

        $args->shouldReceive('getNewValue')
             ->once()
             ->with('finalPrice')
             ->andReturn(220);

        $args->shouldReceive('hasChangedField')
             ->with('price')
             ->andReturn(false);

        $result = $this->inventoryLogService->hasInventoryPriceChanged($args);

        $this->assertTrue($result);
    }

    public function testHasInventoryPriceChangedWhenPriceAndFinalPriceChangedAndOldAndNewValuesAreEqual(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->once()
             ->with('finalPrice')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->once()
             ->with('finalPrice')
             ->andReturn(200);

        $args->shouldReceive('getNewValue')
             ->once()
             ->with('finalPrice')
             ->andReturn(200);

        $args->shouldReceive('hasChangedField')
             ->with('price')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->once()
             ->with('price')
             ->andReturn(100);

        $args->shouldReceive('getNewValue')
             ->once()
             ->with('price')
             ->andReturn(100);

        $result = $this->inventoryLogService->hasInventoryPriceChanged($args);

        $this->assertFalse($result);
    }

    public function testHasInventoryPriceChangedWhenPriceAndFinalPriceChangedAndOldAndNewValuesAreNoEqual(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->with('finalPrice')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->with('finalPrice')
             ->andReturn(200);

        $args->shouldReceive('getNewValue')
             ->with('finalPrice')
             ->andReturn(220);

        $args->shouldReceive('hasChangedField')
             ->with('price')
             ->andReturn(true);

        $args->shouldReceive('getOldValue')
             ->with('price')
             ->andReturn(100);

        $args->shouldReceive('getNewValue')
             ->with('price')
             ->andReturn(110);

        $result = $this->inventoryLogService->hasInventoryPriceChanged($args);

        $this->assertTrue($result);
    }

    public function testCanCallDispatchInventoryPriceChangeMessage(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(LogInventoryPriceChange::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->inventoryLogService->dispatchInventoryPriceChangeMessage(1, 100, 200, 10);
    }


    public function testMakeInventoryPriceHistoryDTOWhenInventoryIsActiveWithAllOptionsAndGetBuyBoxIsFalse(): void
    {
        $userId      = 1;
        $productMock = $this->mockProductEntity();
        $productMock->shouldReceive('getBuyBox')
                    ->withNoArgs()
                    ->andReturn(null);

        $variantMock    = $this->mockProductVariantEntity($productMock);
        $productOptions = $this->mockProductOptions($productMock);
        $variantMock->shouldReceive('getOptionValues')
                    ->withNoArgs()
                    ->andReturn($productOptions);

        $sellerMock = $this->mockSellerEntity();

        $this->mockInventoryEntity($sellerMock, $variantMock);

        $mockInventoryPriceMsg = new LogInventoryPriceChange(10, null, 500, $userId);

        /** @var InventoryPriceHistoryData $result */
        $result = $this->inventoryLogService->makeInventoryPriceHistoryDTO(
            $this->inventoryEntityMock,
            $mockInventoryPriceMsg
        );

        self::assertEquals($result->getIsActive(), true);
        self::assertEquals($result->getPriceFrom(), 100);
        self::assertEquals($result->getFinalPriceFrom(), 500);
        self::assertEquals($result->getIsInventoryBuyBox(), false);
        self::assertEquals($result->getColor(), $productOptions->toArray()[0]);
        self::assertEquals($result->getGuarantee(), $productOptions->toArray()[1]);
        self::assertEquals($result->getSize(), $productOptions->toArray()[2]);
    }


    public function testMakeInventoryPriceHistoryDTOWhenInventoryIsNotActiveWithNoSizeOptionAndGetBuyBoxIsFalse(): void
    {
        $buyboxInventory = Mockery::mock(Inventory::class);
        $buyboxInventory->shouldReceive('getId')
                        ->withNoArgs()
                        ->andReturn(500);
        $productMock = $this->mockProductEntity();
        $productMock->shouldReceive('getBuyBox')
                    ->withNoArgs()
                    ->andReturn($buyboxInventory);

        $variantMock    = $this->mockProductVariantEntity($productMock);
        $productOptions = $this->mockProductOptions($productMock, ["color", "guarantee"]);
        $variantMock->shouldReceive('getOptionValues')
                    ->withNoArgs()
                    ->andReturn($productOptions);

        $sellerMock = $this->mockSellerEntity();

        $this->mockInventoryEntity($sellerMock, $variantMock, "WAIT_FOR_CONFIRM", 4, 1);

        $userId                = 1;
        $mockInventoryPriceMsg = new LogInventoryPriceChange(10, null, null, $userId);

        /** @var InventoryPriceHistoryData $result */
        $result = $this->inventoryLogService->makeInventoryPriceHistoryDTO(
            $this->inventoryEntityMock,
            $mockInventoryPriceMsg
        );

        self::assertEquals($result->getIsActive(), false);
        self::assertEquals($result->getPriceFrom(), 100);
        self::assertEquals($result->getFinalPriceFrom(), 200);
        self::assertEquals($result->getIsInventoryBuyBox(), false);
        self::assertEquals($result->getColor(), $productOptions->toArray()[0]);
        self::assertEquals($result->getGuarantee(), $productOptions->toArray()[1]);
        self::assertEquals($result->getSize(), null);
    }


    public function testMakeInventoryPriceHistoryDTOWhenInventoryIsNotActiveWithNoColorOptionAndGetBuyBoxIsTrue(): void
    {
        $buyboxInventory = Mockery::mock(Inventory::class);
        $buyboxInventory->shouldReceive('getId')
                        ->withNoArgs()
                        ->andReturn(1000);
        $productMock = $this->mockProductEntity();
        $productMock->shouldReceive('getBuyBox')
                    ->withNoArgs()
                    ->andReturn($buyboxInventory);

        $variantMock    = $this->mockProductVariantEntity($productMock);
        $productOptions = $this->mockProductOptions($productMock, ["guarantee", "size"]);
        $variantMock->shouldReceive('getOptionValues')
                    ->withNoArgs()
                    ->andReturn($productOptions);

        $sellerMock = $this->mockSellerEntity();

        $this->mockInventoryEntity($sellerMock, $variantMock, "CONFIRMED", 0, 1);

        $userId                = 1;
        $mockInventoryPriceMsg = new LogInventoryPriceChange(10, 90, 190, $userId);

        /** @var InventoryPriceHistoryData $result */
        $result = $this->inventoryLogService->makeInventoryPriceHistoryDTO(
            $this->inventoryEntityMock,
            $mockInventoryPriceMsg
        );

        self::assertEquals($result->getIsActive(), false);
        self::assertEquals($result->getPriceFrom(), 90);
        self::assertEquals($result->getFinalPriceFrom(), 190);
        self::assertEquals($result->getIsInventoryBuyBox(), true);
        self::assertEquals($result->getColor(), null);
        self::assertEquals($result->getGuarantee(), $productOptions->toArray()[0]);
        self::assertEquals($result->getSize(), $productOptions->toArray()[1]);
    }


    public function testMakeInventoryPriceHistoryDTOWhenInventoryIsNotActiveWithNoOptionsAndGetBuyBoxIsTrue(): void
    {
        $buyboxInventory = Mockery::mock(Inventory::class);
        $buyboxInventory->shouldReceive('getId')
                        ->withNoArgs()
                        ->andReturn(1000);
        $productMock = $this->mockProductEntity();
        $productMock->shouldReceive('getBuyBox')
                    ->withNoArgs()
                    ->andReturn($buyboxInventory);

        $variantMock    = $this->mockProductVariantEntity($productMock);
        $productOptions = $this->mockProductOptions($productMock, []);
        $variantMock->shouldReceive('getOptionValues')
                    ->withNoArgs()
                    ->andReturn($productOptions);

        $sellerMock = $this->mockSellerEntity();

        $this->mockInventoryEntity($sellerMock, $variantMock, "CONFIRMED", 5, 0);

        $userId                = 1;
        $mockInventoryPriceMsg = new LogInventoryPriceChange(10, 90, null, $userId);

        /** @var InventoryPriceHistoryData $result */
        $result = $this->inventoryLogService->makeInventoryPriceHistoryDTO(
            $this->inventoryEntityMock,
            $mockInventoryPriceMsg
        );

        self::assertEquals($result->getIsActive(), false);
        self::assertEquals($result->getPriceFrom(), 90);
        self::assertEquals($result->getFinalPriceFrom(), 200);
        self::assertEquals($result->getIsInventoryBuyBox(), true);
        self::assertEquals($result->getColor(), null);
        self::assertEquals($result->getGuarantee(), null);
        self::assertEquals($result->getSize(), null);
    }


    /**
     * @return Product|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function mockProductEntity(): Product
    {
        $productMock = Mockery::mock(Product::class);
        $productMock->shouldReceive('getId')
                    ->withNoArgs()
                    ->andReturn(6);
        $productMock->shouldReceive('getTitle')
                    ->withNoArgs()
                    ->andReturn("product test");

        return $productMock;
    }

    /**
     * @param $productMock
     *
     * @return ProductVariant|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function mockProductVariantEntity($productMock): ProductVariant
    {
        $variantMock = Mockery::mock(ProductVariant::class);
        $variantMock->shouldReceive('getId')
                    ->withNoArgs()
                    ->andReturn(1);
        $variantMock->shouldReceive('getProduct')
                    ->withNoArgs()
                    ->andReturn($productMock);

        return $variantMock;
    }

    /**
     * @return Seller|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function mockSellerEntity(): Seller
    {
        $sellerMock = Mockery::mock(Seller::class);
        $sellerMock->shouldReceive('getId')
                   ->withNoArgs()
                   ->andReturn(23);
        $sellerMock->shouldReceive('getName')
                   ->withNoArgs()
                   ->andReturn("seller test");

        return $sellerMock;
    }

    protected function mockInventoryEntity(
        $sellerMock,
        $variantMock,
        $status = "CONFIRMED",
        $stock = 5,
        $isActive = 1
    ): void {
        $this->inventoryEntityMock = Mockery::mock(Inventory::class);

        $this->inventoryEntityMock->shouldReceive('getSeller')
                                  ->withNoArgs()
                                  ->andReturn($sellerMock);

        $this->inventoryEntityMock->shouldReceive('getVariant')
                                  ->withNoArgs()
                                  ->andReturn($variantMock);

        $this->inventoryEntityMock->shouldReceive('getId')
                                  ->withNoArgs()
                                  ->andReturn(1000);
        $this->inventoryEntityMock->shouldReceive('getPrice')
                                  ->withNoArgs()
                                  ->andReturn(100);
        $this->inventoryEntityMock->shouldReceive('getFinalPrice')
                                  ->withNoArgs()
                                  ->andReturn(200);
        $this->inventoryEntityMock->shouldReceive('getStatus')
                                  ->withNoArgs()
                                  ->andReturn($status);
        $this->inventoryEntityMock->shouldReceive('getSellerStock')
                                  ->withNoArgs()
                                  ->andReturn($stock);
        $this->inventoryEntityMock->shouldReceive('getIsActive')
                                  ->withNoArgs()
                                  ->andReturn($isActive);
    }


    protected function mockProductOptions($productMock, $options = ["color", "guarantee", "size"]): ArrayCollection
    {
        $newCollection = new ArrayCollection();

        foreach ($options as $option) {
            $productOption = Mockery::mock(ProductOption::class);
            $productOption->shouldReceive('getCode')
                          ->withNoArgs()
                          ->andReturn($option);

            $productOptionValue = Mockery::mock(ProductOptionValue::class);
            $productOptionValue->shouldReceive('getOption')
                               ->withNoArgs()
                               ->andReturn($productOption);

            $newCollection->add($productOptionValue);
        }

        return $newCollection;
    }

    public function testCheckInventoryIsLoggableWhenInventoryHasChanged(): void
    {
        $inventoryId = 1;
        $sellerId    = 2;
        $variantId   = 3;

        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive('getId')
               ->withNoArgs()
               ->once()
               ->andReturn($sellerId);

        $variant = Mockery::mock(ProductVariant::class);
        $variant->shouldReceive('getId')
                ->withNoArgs()
                ->once()
                ->andReturn($variantId);

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getId')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($inventoryId);
        $inventory->shouldReceive('getSeller')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($seller);
        $inventory->shouldReceive('getVariant')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($variant);

        $inventory->shouldReceive('getAllInventoryChangeableProperties')
                  ->withNoArgs()
                  ->once()
                  ->andReturn(['status', 'sellerStock', 'price']);

        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->with('status')
             ->once()
             ->andReturn(true);
        $args->shouldReceive('getNewValue')
             ->with('status')
             ->once()
             ->andReturn('WAITING');
        $args->shouldReceive('getOldValue')
             ->with('status')
             ->once()
             ->andReturn('WAITING');

        $args->shouldReceive('hasChangedField')
             ->with('sellerStock')
             ->once()
             ->andReturn(false);

        $args->shouldReceive('hasChangedField')
             ->with('price')
             ->once()
             ->andReturn(true);
        $args->shouldReceive('getNewValue')
             ->with('price')
             ->twice()
             ->andReturn(3000);
        $args->shouldReceive('getOldValue')
             ->with('price')
             ->twice()
             ->andReturn(2500);

        $result = $this->inventoryLogService->checkInventoryIsLoggable($inventory, $args);

        self::assertTrue($result->isChangeStatus());
        self::assertArrayHasKey('priceFrom', $result->getLoggableProperties());
        self::assertArrayHasKey('priceTo', $result->getLoggableProperties());
        self::assertEquals(2500, $result->getLoggableProperties()['priceFrom']);
        self::assertEquals(3000, $result->getLoggableProperties()['priceTo']);
        self::assertArrayNotHasKey('stockFrom', $result->getLoggableProperties());
        self::assertArrayNotHasKey('stockTo', $result->getLoggableProperties());
        self::assertArrayNotHasKey('statusFrom', $result->getLoggableProperties());
        self::assertArrayNotHasKey('statusTo', $result->getLoggableProperties());
    }

    public function testCheckInventoryIsLoggableWhenInventoryNotHasChanged(): void
    {
        $inventoryId = 1;
        $sellerId    = 2;
        $variantId   = 3;

        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive('getId')
               ->withNoArgs()
               ->once()
               ->andReturn($sellerId);

        $variant = Mockery::mock(ProductVariant::class);
        $variant->shouldReceive('getId')
                ->withNoArgs()
                ->once()
                ->andReturn($variantId);

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getId')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($inventoryId);
        $inventory->shouldReceive('getSeller')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($seller);
        $inventory->shouldReceive('getVariant')
                  ->withNoArgs()
                  ->once()
                  ->andReturn($variant);

        $inventory->shouldReceive('getAllInventoryChangeableProperties')
                  ->withNoArgs()
                  ->once()
                  ->andReturn(['status', 'sellerStock']);

        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
             ->with('status')
             ->once()
             ->andReturn(true);
        $args->shouldReceive('getNewValue')
             ->with('status')
             ->once()
             ->andReturn('WAITING');
        $args->shouldReceive('getOldValue')
             ->with('status')
             ->once()
             ->andReturn('WAITING');

        $args->shouldReceive('hasChangedField')
             ->with('sellerStock')
             ->once()
             ->andReturn(false);

        $result = $this->inventoryLogService->checkInventoryIsLoggable($inventory, $args);

        self::assertFalse($result->isChangeStatus());
        self::assertArrayNotHasKey('statusFrom', $result->getLoggableProperties());
        self::assertArrayNotHasKey('statusTo', $result->getLoggableProperties());
        self::assertArrayNotHasKey('stockFrom', $result->getLoggableProperties());
        self::assertArrayNotHasKey('stockTo', $result->getLoggableProperties());
    }

    public function testItCanCallDispatchInventoryLogMessage(): void
    {
        $inventoryLogData = new InventoryLogData(true, []);
        $this->messageBusMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(LogInventory::class))
                             ->andReturn();

        $this->inventoryLogService->dispatchInventoryLogMessage($inventoryLogData);
    }
}
