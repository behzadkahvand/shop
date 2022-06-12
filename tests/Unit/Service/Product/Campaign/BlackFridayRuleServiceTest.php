<?php

namespace App\Tests\Unit\Service\Product\Campaign;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\Seller;
use App\Exceptions\Product\Campaign\InvalidCampaignRequestException;
use App\Messaging\Handlers\Command\Product\AddBuyBoxToProductHandler;
use App\Messaging\Messages\Command\Product\AddBuyBoxToProduct;
use App\Repository\InventoryRepository;
use App\Service\Product\BuyBox\BuyBoxValidatorService;
use App\Service\Product\Campaign\BlackFridayRequest;
use App\Service\Product\Campaign\BlackFridayRuleService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class BlackFridayRuleServiceTest extends BaseUnitTestCase
{
    private InventoryRepository|LegacyMockInterface|MockInterface|null $inventoryRepository;

    private LegacyMockInterface|MockInterface|BuyBoxValidatorService|null $buyBoxValidator;

    private Inventory|LegacyMockInterface|MockInterface|null $inventory;

    private LegacyMockInterface|BlackFridayRequest|MockInterface|null $blackFridayRequest;

    private Seller|LegacyMockInterface|MockInterface|null $seller;

    private LegacyMockInterface|Product|MockInterface|null $product;

    private BlackFridayRuleService|null $sut;

    private AddBuyBoxToProductHandler|LegacyMockInterface|MockInterface|null $addBuyBoxToProductHandler;

    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blackFridayRequest        = Mockery::mock(BlackFridayRequest::class);
        $this->inventory                 = Mockery::mock(Inventory::class);
        $this->inventoryRepository       = Mockery::mock(InventoryRepository::class);
        $this->buyBoxValidator           = Mockery::mock(BuyBoxValidatorService::class);
        $this->seller                    = Mockery::mock(Seller::class);
        $this->product                   = Mockery::mock(Product::class);
        $this->addBuyBoxToProductHandler = Mockery::mock(AddBuyBoxToProductHandler::class);
        $this->em                        = Mockery::mock(EntityManagerInterface::class);
        $this->sut                       = new BlackFridayRuleService(
            $this->inventoryRepository,
            $this->buyBoxValidator,
            $this->addBuyBoxToProductHandler,
            $this->em
        );
    }

    public function testShouldThrowExceptionIfInventoryIsNotConfirmed(): void
    {
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnFalse();
        $this->expectException(InvalidCampaignRequestException::class);

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldThrowExceptionIfInventoryIsNotActive(): void
    {
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnFalse();
        $this->expectException(InvalidCampaignRequestException::class);

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldOnlyUpdateStockIfInventoryIsAlreadyInCampaign(): void
    {
        $newStock = 5;
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getVariant->getProduct')->withNoArgs()->andReturn($this->product);
        $this->blackFridayRequest->shouldReceive('getStock')->twice()->withNoArgs()->andReturn($newStock);
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();
        $this->inventory->shouldReceive('setSellerStock')->once()->with($newStock)->andReturnSelf();

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldThrowExceptionIfFirstInventoryOfProductIsGoingIntoCampaignButDoesNotMeetBuyBoxCondition(): void
    {
        $oldFinalPrice = 1300;
        $newFinalPrice = 1000;
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->inventory->shouldReceive('getVariant->getProduct')->withNoArgs()->andReturn($this->product);
        $this->inventory->shouldReceive('isBuyBox')->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getPrice')->withNoArgs()->andReturn(10000);
        $this->inventoryRepository->shouldReceive('findOneCampaignInventoryByProduct')->once()->with($this->product)->andReturnNull();
        $this->inventory->shouldReceive('getFinalPrice')->once()->withNoArgs()->andReturn($oldFinalPrice);
        $this->blackFridayRequest->shouldReceive('getFinalPrice')->twice()->withNoArgs()->andReturn($newFinalPrice);
        $this->blackFridayRequest->shouldReceive('getStock')->once()->withNoArgs()->andReturn(4);
        $this->inventory->shouldReceive('setFinalPrice')->once()->with($newFinalPrice)->andReturnSelf();
        $this->buyBoxValidator->shouldReceive('validate')->once()->with($this->product, $this->inventory)->andReturnFalse();
        $this->inventory->shouldReceive('setFinalPrice')->once()->with($oldFinalPrice)->andReturnSelf();

        $this->expectException(InvalidCampaignRequestException::class);

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldWorkWhenFirstInventoryOfProductIsGoingIntoCampaignAndMeetsBuyBoxCondition(): void
    {
        $oldFinalPrice = 1300;
        $newFinalPrice = 1000;
        $newStock      = 5;
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->inventory->shouldReceive('getVariant->getProduct')->withNoArgs()->andReturn($this->product);
        $this->inventory->shouldReceive('isBuyBox')->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getPrice')->withNoArgs()->andReturn(10000);
        $this->inventoryRepository->shouldReceive('findOneCampaignInventoryByProduct')->once()->with($this->product)->andReturnNull();
        $this->inventory->shouldReceive('getFinalPrice')->once()->withNoArgs()->andReturn($oldFinalPrice);
        $this->blackFridayRequest->shouldReceive('getFinalPrice')->times(3)->withNoArgs()->andReturn($newFinalPrice);
        $this->blackFridayRequest->shouldReceive('getStock')->twice()->withNoArgs()->andReturn($newStock);
        $this->inventory->shouldReceive('setFinalPrice')->twice()->with($newFinalPrice)->andReturnSelf();
        $this->buyBoxValidator->shouldReceive('validate')->once()->with($this->product, $this->inventory)->andReturnTrue();
        $this->inventory->shouldReceive('setSellerStock')->once()->with($newStock)->andReturnSelf();
        $this->inventory->shouldReceive('setHasCampaign')->once()->with(true)->andReturnSelf();
        $this->product->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();
        $this->addBuyBoxToProductHandler->shouldReceive('__invoke')->once()->with(AddBuyBoxToProduct::class)->andReturnNull();

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldThrowExceptionIfThereIsAnotherInventoryInCampaignWhichBelongsToAnotherSeller(): void
    {
        $campaignInventory = Mockery::mock(Inventory::class);
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->inventory->shouldReceive('getVariant->getProduct')->withNoArgs()->andReturn($this->product);
        $this->inventory->shouldReceive('getPrice')->withNoArgs()->andReturn(10000);
        $this->blackFridayRequest->shouldReceive('getStock')->once()->withNoArgs()->andReturn(4);
        $this->inventoryRepository
            ->shouldReceive('findOneCampaignInventoryByProduct')
            ->once()
            ->with($this->product)
            ->andReturn($campaignInventory);
        $campaignInventory->shouldReceive('isBelongTo')->with($this->seller)->andReturnFalse();

        $this->expectException(InvalidCampaignRequestException::class);

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldWorkWhenThereIsAnotherInventoryInCampaignWhichBelongsToSameSeller(): void
    {
        $newStock          = 5;
        $newFinalPrice     = 1000;
        $campaignInventory = Mockery::mock(Inventory::class);
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->inventory->shouldReceive('getVariant->getProduct')->withNoArgs()->andReturn($this->product);
        $this->inventory->shouldReceive('getPrice')->withNoArgs()->andReturn(10000);
        $this->inventoryRepository
            ->shouldReceive('findOneCampaignInventoryByProduct')
            ->once()
            ->with($this->product)
            ->andReturn($campaignInventory);
        $campaignInventory->shouldReceive('isBelongTo')->with($this->seller)->andReturnTrue();
        $this->blackFridayRequest->shouldReceive('getStock')->twice()->withNoArgs()->andReturn($newStock);
        $this->blackFridayRequest->shouldReceive('getFinalPrice')->twice()->withNoArgs()->andReturn($newFinalPrice);
        $this->inventory->shouldReceive('setFinalPrice')->once()->with($newFinalPrice)->andReturnSelf();
        $this->inventory->shouldReceive('setSellerStock')->once()->with($newStock)->andReturnSelf();
        $this->inventory->shouldReceive('setHasCampaign')->once()->with(true)->andReturnSelf();
        $this->product->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->addBuyBoxToProductHandler->shouldReceive('__invoke')->once()->with(AddBuyBoxToProduct::class)->andReturnNull();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturnNull();

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }

    public function testShouldThrowExceptionIfFinalPriceIsNotSmallerThanInitialPrice(): void
    {
        $initialPrice  = 1000;
        $newFinalPrice = 1100;
        $this->inventory->shouldReceive('isConfirmed')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getIsActive')->once()->withNoArgs()->andReturnTrue();
        $this->inventory->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($this->seller);
        $this->inventory->shouldReceive('getVariant->getProduct')->withNoArgs()->andReturn($this->product);
        $this->inventory->shouldReceive('isBuyBox')->withNoArgs()->andReturnFalse();
        $this->inventory->shouldReceive('getPrice')->withNoArgs()->andReturn($initialPrice);
        $this->inventoryRepository->shouldReceive('findOneCampaignInventoryByProduct')->once()->with($this->product)->andReturnNull();
        $this->blackFridayRequest->shouldReceive('getFinalPrice')->once()->withNoArgs()->andReturn($newFinalPrice);
        $this->blackFridayRequest->shouldReceive('getStock')->once()->withNoArgs()->andReturn(5);

        $this->expectException(InvalidCampaignRequestException::class);

        $this->sut->apply($this->inventory, $this->blackFridayRequest);
    }
}
