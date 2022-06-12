<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Dictionary\OrderPaymentMethod;
use App\Entity\Configuration;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Order\Stages\ChangePaymentMethodStage;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

final class ChangePaymentMethodStageTest extends BaseUnitTestCase
{
    private LegacyMockInterface|MockInterface|ConfigurationServiceInterface|null $configurationServiceMock;

    private Order|LegacyMockInterface|MockInterface|null $orderMock;

    private OrderDocument|LegacyMockInterface|MockInterface|null $orderDocumentMock;

    private LegacyMockInterface|CustomerAddress|MockInterface|null $customerAddressMock;

    private AbstractPipelinePayload|LegacyMockInterface|MockInterface|null $payloadMock;

    private Configuration|LegacyMockInterface|MockInterface|null $configurationMock;

    private ?ChangePaymentMethodStage $sut;

    protected function setUp(): void
    {
        $this->configurationServiceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->orderMock                = Mockery::mock(Order::class);
        $this->orderDocumentMock        = Mockery::mock(OrderDocument::class);
        $this->customerAddressMock      = Mockery::mock(CustomerAddress::class);
        $this->payloadMock              = Mockery::mock(AbstractPipelinePayload::class);
        $this->configurationMock        = Mockery::mock(Configuration::class);

        $this->sut = new ChangePaymentMethodStage($this->configurationServiceMock);
    }

    public function testGetPriorityAndTag(): void
    {
        self::assertEquals(-25, $this->sut::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', $this->sut::getTag());
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOnlineAndOrderAmountIsGreaterThanFiftyMillion(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::OFFLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::ONLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::OFFLINE)->andReturnSelf();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOnlineAndOrderAmountIsZero(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::OFFLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::ONLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(0);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::OFFLINE)->andReturnSelf();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(0);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsOnlineAndOrderPayableIsZero(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::ONLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(0);

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(300_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderAmountIsGreaterThanFiftyMillion(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);
        $this->orderMock->expects('getShipmentsCount')->withNoArgs()->andReturns(1);

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnTrue();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderAmountIsFiftyMillion(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_000_000);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_000_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderAmountIsBetweenThreeAndFiftyMillion(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(10_000_000);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(10_000_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderAmountIsThreeMillion(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(3_000_000);
        $this->orderMock->expects('getShipmentsCount')->withNoArgs()->andReturns(1);

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnTrue();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(3_000_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderAmountIsLessThanThreeMillion(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(2_900_000);
        $this->orderMock->expects('getShipmentsCount')->withNoArgs()->andReturns(1);

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnTrue();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(2_900_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOfflineAndCustomerAddressIsNotExpress(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(2_900_000);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnFalse();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(2_900_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderHasSeveralShipments(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(2_900_000);
        $this->orderMock->expects('getShipmentsCount')->withNoArgs()->andReturns(2);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnTrue();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(2_900_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderAmountIsZero(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getShipmentsCount')->withNoArgs()->andReturns(1);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(0);

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnTrue();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(0);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsOfflineAndOrderPayableIsZero(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('getCustomerAddress')->withNoArgs()->andReturns($this->customerAddressMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(0);
        $this->orderMock->expects('getShipmentsCount')->withNoArgs()->andReturns(1);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->customerAddressMock->expects('isCityExpress')->withNoArgs()->andReturnTrue();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(300_000);

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsCPGAndNoConfigurationSet(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::CPG);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $this->configurationServiceMock->expects('findByCode')
                                       ->with(ConfigurationCodeDictionary::CPG_GATEWAY_AVAILABILITY)
                                       ->andReturnNull();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsCPGAndConfigurationHasTrueValue(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::CPG);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $this->configurationServiceMock->expects('findByCode')
                                       ->with(ConfigurationCodeDictionary::CPG_GATEWAY_AVAILABILITY)
                                       ->andReturns($this->configurationMock);

        $this->configurationMock->expects('getValue')->withNoArgs()->andReturnTrue();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsCPGAndConfigurationHasFalseValue(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::CPG);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $this->configurationServiceMock->expects('findByCode')
                                       ->with(ConfigurationCodeDictionary::CPG_GATEWAY_AVAILABILITY)
                                       ->andReturns($this->configurationMock);

        $this->configurationMock->expects('getValue')->withNoArgs()->andReturnFalse();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsHamrahCardAndNoConfigurationSet(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::HAMRAH_CARD);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $this->configurationServiceMock->expects('findByCode')
                                       ->with(ConfigurationCodeDictionary::HAMRAH_CARD_GATEWAY_AVAILABILITY)
                                       ->andReturnNull();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanNotChangePaymentMethodWhenPaymentMethodIsHamrahCardAndConfigurationHasTrueValue(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::HAMRAH_CARD);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $this->configurationServiceMock->expects('findByCode')
                                       ->with(ConfigurationCodeDictionary::HAMRAH_CARD_GATEWAY_AVAILABILITY)
                                       ->andReturns($this->configurationMock);

        $this->configurationMock->expects('getValue')->withNoArgs()->andReturnTrue();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangePaymentMethodWhenPaymentMethodIsHamrahCardAndConfigurationHasFalseValue(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);
        $this->payloadMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderMock->expects('getOrderDocument')->withNoArgs()->andReturns($this->orderDocumentMock);
        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::HAMRAH_CARD);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(50_001_000);
        $this->orderMock->expects('setPaymentMethod')->with(OrderPaymentMethod::ONLINE)->andReturnSelf();

        $this->orderDocumentMock->expects('getAmount')->withNoArgs()->andReturns(50_001_000);

        $this->configurationServiceMock->expects('findByCode')
                                       ->with(ConfigurationCodeDictionary::HAMRAH_CARD_GATEWAY_AVAILABILITY)
                                       ->andReturns($this->configurationMock);

        $this->configurationMock->expects('getValue')->withNoArgs()->andReturnFalse();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }
}
