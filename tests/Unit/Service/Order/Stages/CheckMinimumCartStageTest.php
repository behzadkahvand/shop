<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Configuration;
use App\Exceptions\Order\MinimumOrderException;
use App\Service\Configuration\ConfigurationService;
use App\Service\Order\Stages\CheckMinimumCartStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class CheckMinimumCartStageTest extends BaseUnitTestCase
{
    private AbstractPipelinePayload|Mockery\LegacyMockInterface|Mockery\MockInterface|null $payloadMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|ConfigurationService|null $configurationServiceMock;
    private Configuration|Mockery\LegacyMockInterface|Mockery\MockInterface|null $configurationMock;
    private Cart|Mockery\LegacyMockInterface|Mockery\MockInterface|null $cartMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|CartItem|null $cartItemMock;

    protected function setUp(): void
    {
        $this->markTestSkipped();

        parent::setUp();
        $this->cartMock = Mockery::mock(Cart::class);
        $this->cartItemMock = Mockery::mock(CartItem::class);
        $this->payloadMock = Mockery::mock(AbstractPipelinePayload::class);
        $this->configurationServiceMock = Mockery::mock(ConfigurationService::class);
        $this->configurationMock = Mockery::mock(Configuration::class);
    }

    public function testItCanApplyMinimumOrderConditions(): void
    {
        $this->payloadMock->expects('getCart')->withNoArgs()->andReturn($this->cartMock);
        $this->cartMock->expects('getItemsGrandTotal')->andReturn(100);
        $this->configurationServiceMock->expects('findByCode')->andReturn($this->configurationMock);
        $this->configurationMock->expects('getValue')->andReturn(100);
        $stage = new CheckMinimumCartStage($this->configurationServiceMock);

        self::assertSame($this->payloadMock, $stage($this->payloadMock));
    }

    public function testItCanApplyMinimumOrderConditionsWithoutConfiguration(): void
    {
        $this->payloadMock->expects('getCart')->withNoArgs()->andReturn($this->cartMock);
        $this->cartMock->expects('getItemsGrandTotal')->andReturn(100);
        $this->configurationServiceMock->expects('findByCode')->andReturnNull();

        $stage = new CheckMinimumCartStage($this->configurationServiceMock);

        self::assertSame($this->payloadMock, $stage($this->payloadMock));
    }

    public function testItCanExceptionMinimumOrderConditions(): void
    {
        self::expectException(MinimumOrderException::class);
        $this->payloadMock->expects('getCart')->withNoArgs()->andReturn($this->cartMock);
        $this->cartMock->expects('getItemsGrandTotal')->andReturn(99);
        $this->configurationServiceMock->expects('findByCode')->andReturn($this->configurationMock);
        $this->configurationMock->expects('getValue')->andReturn(100);
        $stage = new CheckMinimumCartStage($this->configurationServiceMock);
        $stage($this->payloadMock);
    }
}
