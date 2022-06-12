<?php

namespace App\Tests\Unit\Service\Order\Wallet;

use App\Dictionary\TransactionStatus;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Entity\RefundDocument;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Service\Notification\DTOs\Customer\Wallet\WalletDepositNotificationDTO;
use App\Service\Notification\DTOs\Customer\Wallet\WalletWithdrawNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\Wallet\OrderWalletPaymentService;
use App\Service\Payment\TransactionIdentifierService;
use App\Tests\Unit\BaseUnitTestCase;
use App\Tests\Unit\TestDoubles\Builders\TransferRequestBuilder;
use App\Tests\Unit\TestDoubles\Stubs\OrderWalletPaymentServiceStub;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class OrderWalletPaymentServiceTest extends BaseUnitTestCase
{
    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $manager;
    private TransactionIdentifierService|LegacyMockInterface|MockInterface|null $transactionIdentifier;
    private Order|LegacyMockInterface|MockInterface|null $order;
    private LegacyMockInterface|MockInterface|RefundDocument|null $refundDocument;
    private LegacyMockInterface|MockInterface|Wallet|null $wallet;
    private LegacyMockInterface|MockInterface|Customer|null $customer;
    private LegacyMockInterface|Transaction|MockInterface|null $transaction;
    private OrderDocument|LegacyMockInterface|MockInterface|null $orderDocument;
    private OrderWalletPaymentService|null $sut;
    private NotificationService|LegacyMockInterface|MockInterface|null $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refundDocument = Mockery::mock(RefundDocument::class);
        $this->order = Mockery::mock(Order::class);
        $this->wallet = Mockery::mock(Wallet::class);
        $this->customer = Mockery::mock(Customer::class);
        $this->transaction = Mockery::mock(Transaction::class);
        $this->orderDocument = Mockery::mock(OrderDocument::class);
        $this->manager = Mockery::mock(EntityManagerInterface::class);
        $this->transactionIdentifier = Mockery::mock(TransactionIdentifierService::class);
        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->sut = new OrderWalletPaymentServiceStub(
            $this->manager,
            $this->transactionIdentifier,
            $this->refundDocument,
            $this->notificationService
        );
    }

    public function testDepositShouldWorkCorrectly(): void
    {
        $amount = 1000;
        $transactionId = 5;
        $transferRequest = TransferRequestBuilder::of($amount);
        $this->manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('getWallet')->once()->withNoArgs()->andReturn($this->wallet);
        $this->wallet->shouldReceive('deposit')->once()->with($transferRequest)->andReturnNull();
        $this->order->shouldReceive('addRefundDocument')->once()->with($this->refundDocument)->andReturnSelf();
        $this->refundDocument->shouldReceive('setAmount')->once()->with($amount)->andReturnSelf();
        $this->refundDocument->shouldReceive('newTransaction')->once()->withNoArgs()->andReturn($this->transaction);
        $this->transaction->shouldReceive('setAmount')->once()->with($amount)->andReturnSelf();
        $this->transaction->shouldReceive('setGateway')->once()->with(Wallet::GATEWAY_NAME)->andReturnSelf();
        $this->transaction->shouldReceive('setStatus')->once()->with(TransactionStatus::SUCCESS)->andReturnSelf();
        $this->transaction->shouldReceive('setPaidAt')->once()->withAnyArgs()->andReturnSelf();
        $this->manager->shouldReceive('lock')->once()->with(Wallet::class, LockMode::PESSIMISTIC_READ)->andReturnNull();
        $this->manager->shouldReceive('persist')->once()->with($this->refundDocument)->andReturnNull();
        $this->manager->shouldReceive('persist')->once()->with($this->transaction)->andReturnNull();
        $this->manager->shouldReceive('flush')->twice()->withNoArgs()->andReturnNull();
        $this->transactionIdentifier->shouldReceive('generateIdentifier')->once()->with($this->transaction)->andReturn($transactionId);
        $this->transaction->shouldReceive('setIdentifier')->once()->with($transactionId)->andReturnSelf();
        $this->manager->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
        $this->notificationService->shouldReceive('send')->once()->with(WalletDepositNotificationDTO::class)->andReturnNull();

        $this->sut->deposit($this->order, $transferRequest);
    }

    public function testDepositShouldRollbackTransactionAndThrowExceptionIfAnyExceptionOccurred(): void
    {
        $amount = 1000;
        $this->manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andThrow(new Exception());
        $this->manager->shouldReceive('close')->once()->withNoArgs()->andReturnNull();
        $this->manager->shouldReceive('rollback')->once()->withNoArgs()->andReturnNull();

        $this->expectException(Exception::class);

        $this->sut->deposit($this->order, TransferRequestBuilder::of($amount));
    }

    public function testDepositShouldDoNothingIfAmountIsEqualToZero(): void
    {
        $this->expectNotToPerformAssertions();

        $this->sut->deposit($this->order, TransferRequestBuilder::of(0));
    }

    public function testDepositShouldDoNothingIfAmountIsSmallerThanZero(): void
    {
        $this->expectNotToPerformAssertions();

        $this->sut->deposit($this->order, TransferRequestBuilder::of(-100));
    }

    public function testWithdrawShouldWorkCorrectly(): void
    {
        $amount = 1000;
        $transactionId = 5;
        $transferRequest = TransferRequestBuilder::of($amount);
        $this->manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andReturn($this->orderDocument);
        $this->order->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->customer->shouldReceive('getWallet')->once()->withNoArgs()->andReturn($this->wallet);
        $this->wallet->shouldReceive('withdraw')->once()->with($transferRequest)->andReturnNull();
        $this->orderDocument->shouldReceive('newTransaction')->once()->withNoArgs()->andReturn($this->transaction);
        $this->transaction->shouldReceive('setAmount')->once()->with($amount)->andReturnSelf();
        $this->transaction->shouldReceive('setGateway')->once()->with(Wallet::GATEWAY_NAME)->andReturnSelf();
        $this->transaction->shouldReceive('setStatus')->once()->with(TransactionStatus::SUCCESS)->andReturnSelf();
        $this->transaction->shouldReceive('setPaidAt')->once()->withAnyArgs()->andReturnSelf();
        $this->manager->shouldReceive('lock')->once()->with(Wallet::class, LockMode::PESSIMISTIC_READ)->andReturnNull();
        $this->manager->shouldReceive('persist')->once()->with($this->transaction)->andReturnNull();
        $this->manager->shouldReceive('flush')->twice()->withNoArgs()->andReturnNull();
        $this->transactionIdentifier->shouldReceive('generateIdentifier')->once()->with($this->transaction)->andReturn($transactionId);
        $this->transaction->shouldReceive('setIdentifier')->once()->with($transactionId)->andReturnSelf();
        $this->manager->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
        $this->notificationService->shouldReceive('send')->once()->with(WalletWithdrawNotificationDTO::class)->andReturnNull();

        $this->sut->withdraw($this->order, $transferRequest);
    }

    public function testWithdrawShouldRollbackTransactionAndThrowExceptionIfAnyExceptionOccurred(): void
    {
        $amount = 1000;
        $this->manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
        $this->order->shouldReceive('getOrderDocument')->once()->withNoArgs()->andThrow(new Exception());
        $this->manager->shouldReceive('close')->once()->withNoArgs()->andReturnNull();
        $this->manager->shouldReceive('rollback')->once()->withNoArgs()->andReturnNull();

        $this->expectException(Exception::class);

        $this->sut->withdraw($this->order, TransferRequestBuilder::of($amount));
    }

    public function testWithdrawShouldDoNothingIfAmountIsEqualToZero(): void
    {
        $this->expectNotToPerformAssertions();

        $this->sut->withdraw($this->order, TransferRequestBuilder::of(0));
    }

    public function testWithdrawShouldDoNothingIfAmountIsSmallerThanZero(): void
    {
        $this->expectNotToPerformAssertions();

        $this->sut->withdraw($this->order, TransferRequestBuilder::of(-100));
    }
}
