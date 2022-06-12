<?php

namespace App\Tests\Unit\Service\Order\Wallet;

use App\DTO\Wallet\TransferRequest;
use App\Entity\Customer;
use App\Entity\Order;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\Order\Wallet\OrderWalletPaymentService;
use App\Service\Order\Wallet\TransferRequestFactory;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class OrderWalletPaymentHandlerTest extends BaseUnitTestCase
{
    private LegacyMockInterface|MockInterface|OrderWalletPaymentService|null $walletPaymentService;
    private Order|LegacyMockInterface|MockInterface|null $order;
    private OrderWalletPaymentHandler|null $sut;
    private LegacyMockInterface|MockInterface|null $transferRequestFactory;
    private LegacyMockInterface|MockInterface|null $transferRequest;

    protected function setUp(): void
    {
        $this->walletPaymentService = Mockery::mock(OrderWalletPaymentService::class);
        $this->order = Mockery::mock(Order::class);
        $this->transferRequestFactory = Mockery::mock(TransferRequestFactory::class);
        $this->transferRequest = Mockery::mock(TransferRequest::class);

        $this->sut = new OrderWalletPaymentHandler($this->walletPaymentService, $this->transferRequestFactory);
    }

    public function testShouldDoNothingIfWalletPaymentIsSetToFalse(): void
    {
        $this->order->shouldReceive('hasWalletPayment')->once()->withNoArgs()->andReturnFalse();

        $this->sut->handle($this->order, 'dummy reason');
    }

    public function testShouldDoNothingIfOrderPayableIsZero(): void
    {
        $this->order->shouldReceive('hasWalletPayment')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getPayable')->once()->withNoArgs()->andReturn(0);

        $this->sut->handle($this->order, 'dummy reason');
    }

    /**
     * @dataProvider withdrawDataProvider
     */
    public function testShouldWithdrawFromWalletIfOrderPayableIsAPositiveAmount(
        int $walletBalance,
        int $orderPayable,
        int $withdrawalAmount
    ): void {
        $customer = Mockery::mock(Customer::class);
        $this->order->shouldReceive('hasWalletPayment')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getPayable')->once()->withNoArgs()->andReturn($orderPayable);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($customer);
        $customer->shouldReceive('getWalletBalance')->once()->withNoArgs()->andReturn($walletBalance);

        $reason = 'dummy reason';
        $this->transferRequestFactory
            ->shouldReceive('make')
            ->once()
            ->with($withdrawalAmount, $reason, null, $this->order)
            ->andReturn($this->transferRequest);

        $this->walletPaymentService->shouldReceive('withdraw')->once()->with($this->order, $this->transferRequest)->andReturnNull();

        $this->sut->handle($this->order, $reason);
    }

    public function withdrawDataProvider(): array
    {
        return [
            ['walletBalance' => 1000, 'orderPayable' => 600, 'withdrawalAmount' => 600],
            ['walletBalance' => 1000, 'orderPayable' => 1200, 'withdrawalAmount' => 1000],
            ['walletBalance' => 0, 'orderPayable' => 1200, 'withdrawalAmount' => 0],
        ];
    }

    /**
     * @dataProvider depositDataProvider
     */
    public function testShouldDepositIntoWalletIfOrderPayableIsANegativeAmount(
        int $walletPayments,
        int $walletRefunds,
        int $orderPayable,
        int $depositAmount
    ): void {
        $this->order->shouldReceive('hasWalletPayment')->once()->withNoArgs()->andReturnTrue();
        $this->order->shouldReceive('getPayable')->once()->withNoArgs()->andReturn($orderPayable);
        $this->order->shouldReceive('calculateWalletPayments')->once()->withNoArgs()->andReturn($walletPayments);
        $this->order->shouldReceive('calculateWalletRefunds')->once()->withNoArgs()->andReturn($walletRefunds);

        $reason = 'dummy reason';
        $this->transferRequestFactory
            ->shouldReceive('make')
            ->once()
            ->with($depositAmount, $reason, null, $this->order)
            ->andReturn($this->transferRequest);

        $this->walletPaymentService->shouldReceive('deposit')->once()->with($this->order, $this->transferRequest)->andReturnNull();

        $this->sut->handle($this->order, $reason);
    }

    public function depositDataProvider(): array
    {
        return [
            ['walletPayments' => 1000, 'walletRefunds' => 500, 'orderPayable' => -300, 'depositAmount' => 300],
            ['walletPayments' => 1000, 'walletRefunds' => 500, 'orderPayable' => -800, 'depositAmount' => 500],
            ['walletPayments' => 1000, 'walletRefunds' => 1000, 'orderPayable' => -800, 'depositAmount' => 0],
        ];
    }
}
