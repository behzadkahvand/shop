<?php

namespace App\Tests\Unit\Service\Product\BuyBox;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\Product\BuyBox\BuyBoxValidatorService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BuyBoxValidatorServiceTest extends MockeryTestCase
{
    /**
     * @var Product|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $productMock;

    /**
     * @var Inventory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $inventoryMock;

    protected BuyBoxValidatorService $buyBoxValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productMock   = Mockery::mock(Product::class);
        $this->inventoryMock = Mockery::mock(Inventory::class);

        $this->buyBoxValidator = new BuyBoxValidatorService();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->buyBoxValidator);

        $this->productMock   = null;
        $this->inventoryMock = null;
    }

    public function testItCanValidateBuyBoxWhenProductHasNotAnyBuyBox(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturnNull();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }

    public function testItCanValidateBuyBoxWhenProductBuyBoxIsUnavailable(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxIsSameAsProductBuyBox(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 52);

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertFalse($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInFirstPeriodPricesAndBuyBoxIsInvalid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(80_000, 80_050);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertFalse($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInSecondPeriodPricesAndBuyBoxIsInvalid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(900_000, 904_500);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertFalse($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInThirdPeriodPricesAndBuyBoxIsInvalid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(25_000_000, 25_009_500);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertFalse($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInFourthPeriodPricesAndBuyBoxIsInvalid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(50_000_000, 50_019_500);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertFalse($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInFirstPeriodPricesAndBuyBoxIsValid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(80_000, 80_100);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInSecondPeriodPricesAndBuyBoxIsValid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(900_000, 905_000);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInThirdPeriodPricesAndBuyBoxIsValid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(25_000_000, 25_010_000);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }

    public function testItCanValidateBuyBoxWhenBuyBoxDiffPriceInFourthPeriodPricesAndBuyBoxIsValid(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(50_000_000, 50_020_000);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnFalse();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }

    public function testShouldReturnTrueWhenBuyBoxIsInCampaignAndHasTheSamePriceAsProductBuyBox(): void
    {
        $this->productMock->shouldReceive('getBuyBox')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->inventoryMock);

        $this->inventoryMock->shouldReceive('isAvailable')
                            ->once()
                            ->withNoArgs()
                            ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(52, 100);
        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(50_000_000, 50_000_000);

        $this->inventoryMock->shouldReceive('getHasCampaign')->once()->withNoArgs()->andReturnTrue();

        $result = $this->buyBoxValidator->validate($this->productMock, $this->inventoryMock);

        self::assertTrue($result);
    }
}
