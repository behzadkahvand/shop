<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\TransferReason;
use App\DTO\Wallet\TransferRequest;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\CheckForWalletPaymentStage;
use App\Service\Order\Wallet\OrderWalletPaymentService;
use App\Service\Order\Wallet\TransferRequestFactory;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class CheckForWalletPaymentStageTest extends BaseUnitTestCase
{
    private const GRAND_TOTAL = 10000;
    private const WALLET_BALANCE = 12000;
    private const MINIMUM_ONLINE_PAYABLE = 1000;

    private LegacyMockInterface|MockInterface|CreateOrderPayload|null $payload;

    private Order|LegacyMockInterface|MockInterface|null $order;

    private LegacyMockInterface|MockInterface|Customer|null $customer;

    private LegacyMockInterface|MockInterface|OrderWalletPaymentService|null $walletPaymentService;

    private LegacyMockInterface|MockInterface|null $transferRequestFactory;

    private LegacyMockInterface|MockInterface|null $transferRequest;

    private CheckForWalletPaymentStage|null $sut;

    private OrderDocument|LegacyMockInterface|MockInterface|null $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order                 = Mockery::mock(Order::class);
        $this->document              = Mockery::mock(OrderDocument::class);
        $this->customer              = Mockery::mock(Customer::class);
        $this->payload               = Mockery::mock(CreateOrderPayload::class);
        $this->walletPaymentService  = Mockery::mock(OrderWalletPaymentService::class);
        $this->transferRequestFactory = Mockery::mock(TransferRequestFactory::class);
        $this->transferRequest = Mockery::mock(TransferRequest::class);

        $this->sut = new CheckForWalletPaymentStage($this->walletPaymentService, $this->transferRequestFactory);
    }

    public function testShouldDoNothingIfCustomerDoesNotHaveWallet(): void
    {
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnFalse();

        $this->sut->__invoke($this->payload);
    }

    public function testShouldDoNothingIfUseWalletFlagIsSetToFalse(): void
    {
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->document);
        $this->document->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(self::GRAND_TOTAL);
        $this->customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn(self::WALLET_BALANCE);
        $this->payload->shouldReceive('useWallet')->once()->withNoArgs()->andReturnFalse();

        $this->sut->__invoke($this->payload);
    }

    public function testShouldDoNothingIfWalletHasNoCredit(): void
    {
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->document);
        $this->document->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(self::GRAND_TOTAL);
        $this->customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn(0);
        $this->payload->shouldReceive('useWallet')->once()->withNoArgs()->andReturnTrue();

        $this->sut->__invoke($this->payload);
    }

    public function testShouldWorkWhenWalletBalanceIsGreaterThanOrderPayable(): void
    {
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->document);
        $this->document->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(self::GRAND_TOTAL);
        $this->customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn(self::WALLET_BALANCE);
        $this->payload->shouldReceive('useWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('setHasWalletPayment')->once()->with(true)->andReturnSelf();
        $this->transferRequestFactory
            ->shouldReceive('make')
            ->once()
            ->with(self::GRAND_TOTAL, TransferReason::ORDER_PURCHASE, null, $this->order)
            ->andReturn($this->transferRequest);
        $this->walletPaymentService->shouldReceive('withdraw')->once()->with($this->order, $this->transferRequest);

        $payload = $this->sut->__invoke($this->payload);

        self::assertSame($this->payload, $payload);
    }

    public function testShouldWorkWhenWalletBalanceIsSmallerThanOrderPayable(): void
    {
        $walletBalance = 8000;
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->document);
        $this->document->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(self::GRAND_TOTAL);
        $this->customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn($walletBalance);
        $this->payload->shouldReceive('useWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('setHasWalletPayment')->once()->with(true)->andReturnSelf();
        $this->transferRequestFactory
            ->shouldReceive('make')
            ->once()
            ->with($walletBalance, TransferReason::ORDER_PURCHASE, null, $this->order)
            ->andReturn($this->transferRequest);
        $this->walletPaymentService->shouldReceive('withdraw')->once()->with($this->order, $this->transferRequest);

        $payload = $this->sut->__invoke($this->payload);

        self::assertSame($this->payload, $payload);
    }

    public function testShouldWorkWhenWalletBalanceIsSmallerThanOrderPayableButMinimumOnlinePayableConditionIsNotMet(): void
    {
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->document);
        $this->document->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(self::GRAND_TOTAL);
        $walletBalance = self::GRAND_TOTAL - self::MINIMUM_ONLINE_PAYABLE - 500;
        $this->customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn($walletBalance);
        $this->payload->shouldReceive('useWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('setHasWalletPayment')->once()->with(true)->andReturnSelf();
        $this->transferRequestFactory
            ->shouldReceive('make')
            ->once()
            ->with($walletBalance, TransferReason::ORDER_PURCHASE, null, $this->order)
            ->andReturn($this->transferRequest);
        $this->walletPaymentService->shouldReceive('withdraw')->once()->with($this->order, $this->transferRequest);

        $payload = $this->sut->__invoke($this->payload);

        self::assertSame($this->payload, $payload);
    }

    public function testShouldWorkWhenWalletBalanceIsSmallerThanOrderPayableAndMinimumOnlinePayableConditionIsMet(): void
    {
        $walletBalance = self::GRAND_TOTAL - self::MINIMUM_ONLINE_PAYABLE + 500;
        $walletPayable = self::GRAND_TOTAL - self::MINIMUM_ONLINE_PAYABLE;
        $this->payload->shouldReceive('getOrder')->once()->withNoArgs()->andReturn($this->order);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('hasWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->document);
        $this->document->shouldReceive('getAmount')->once()->withNoArgs()->andReturn(self::GRAND_TOTAL);
        $this->customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn($walletBalance);
        $this->payload->shouldReceive('useWallet')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('setHasWalletPayment')->once()->with(true)->andReturnSelf();
        $this->transferRequestFactory
            ->shouldReceive('make')
            ->once()
            ->with($walletPayable, TransferReason::ORDER_PURCHASE, null, $this->order)
            ->andReturn($this->transferRequest);
        $this->walletPaymentService->shouldReceive('withdraw')->once()->with($this->order, $this->transferRequest);

        $payload = $this->sut->__invoke($this->payload);

        self::assertSame($this->payload, $payload);
    }

    public function testGetPriorityAndTag(): void
    {
        self::assertEquals(-20, $this->sut::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', $this->sut::getTag());
    }
}
