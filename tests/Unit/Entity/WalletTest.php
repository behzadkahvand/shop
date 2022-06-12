<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Wallet;
use App\Entity\WalletHistory;
use App\Exceptions\Wallet\InvalidWalletTransactionException;
use App\Tests\Unit\BaseUnitTestCase;
use App\Tests\Unit\TestDoubles\Builders\TransferRequestBuilder;

class WalletTest extends BaseUnitTestCase
{
    public function testShouldHaveZeroBalanceAfterConstruction(): void
    {
        $sut = new Wallet();

        self::assertEquals(0, $sut->getBalance());
    }

    public function testDepositShouldDoNothingIfAmountIsZero(): void
    {
        $sut = new Wallet();

        $sut->deposit(TransferRequestBuilder::of(0));

        self::assertCount(0, $sut->getHistories());
    }

    public function testDepositShouldThrowExceptionIfAmountIsNegative(): void
    {
        $sut = new Wallet();

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('Can not deposit a negative amount into wallet!');

        $sut->deposit(TransferRequestBuilder::of(-100));
    }

    public function testDepositShouldIncreaseWalletBalanceAndAddANewHistory(): void
    {
        $depositAmount = 100;
        $sut = new Wallet();

        $sut->deposit(TransferRequestBuilder::of($depositAmount));

        self::assertEquals($depositAmount, $sut->getBalance());
        $histories = $sut->getHistories();
        self::assertCount(1, $histories);
        self::assertEquals(WalletHistory::DEPOSIT, $histories[0]->getType());
        self::assertEquals($depositAmount, $histories[0]->getAmount());
    }

    public function testWithdrawShouldDoNothingIfAmountIsZero(): void
    {
        $sut = new Wallet();

        $sut->withdraw(TransferRequestBuilder::of(0));

        self::assertCount(0, $sut->getHistories());
    }

    public function testWithdrawShouldThrowExceptionIfAmountIsNegative(): void
    {
        $sut = new Wallet();

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('Can not withdraw a negative amount from wallet!');

        $sut->withdraw(TransferRequestBuilder::of(-100));
    }

    public function testWithdrawShouldDecreaseWalletBalanceAndAddANewHistory(): void
    {
        $initialAmount = 1000;
        $withdrawAmount = 100;
        $sut = new Wallet();
        $sut->deposit(TransferRequestBuilder::of($initialAmount));

        $sut->withdraw(TransferRequestBuilder::of($withdrawAmount));

        self::assertEquals($initialAmount - $withdrawAmount, $sut->getBalance());
        $histories = $sut->getHistories();
        self::assertCount(2, $histories);
        self::assertEquals(WalletHistory::WITHDRAW, $histories[1]->getType());
        self::assertEquals($withdrawAmount, $histories[1]->getAmount());
    }

    public function testWithdrawShouldThrowExceptionIfAmountIsMoreThanWalletBalance(): void
    {
        $initialAmount = 10000;
        $sut = new Wallet();
        $sut->deposit(TransferRequestBuilder::of($initialAmount));

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('Insufficient wallet balance.');

        $sut->withdraw(TransferRequestBuilder::of($initialAmount + 500));
    }

    public function testShouldSetBeforeAndAfterAmountOnHistoryCorrectly(): void
    {
        $initialAmount = 1000;
        $sut = new Wallet();
        $sut->deposit(TransferRequestBuilder::of($initialAmount));

        self::assertEquals(0, $sut->getHistories()[0]->getBeforeAmount());
        self::assertEquals(1000, $sut->getHistories()[0]->getAfterAmount());

        $withdrawAmount = 200;
        $sut->withdraw(TransferRequestBuilder::of($withdrawAmount));
        self::assertEquals(1000, $sut->getHistories()[1]->getBeforeAmount());
        self::assertEquals(800, $sut->getHistories()[1]->getAfterAmount());
    }

    public function testShouldNotBeFrozenWhenCreated(): void
    {
        $sut = new Wallet();

        self::assertFalse($sut->isFrozen());
    }

    public function testFreezeShouldMakeWalletFrozen(): void
    {
        $sut = new Wallet();

        $sut->freeze();

        self::assertTrue($sut->isFrozen());
    }

    public function testDepositShouldThrowExceptionIfWalletIsFrozen(): void
    {
        $sut = new Wallet();

        $sut->freeze();

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('Wallet is frozen');

        $sut->deposit(TransferRequestBuilder::of(100));
    }

    public function testWithdrawShouldThrowExceptionIfWalletIsFrozen(): void
    {
        $sut = new Wallet();
        $sut->deposit(TransferRequestBuilder::of(1000));

        $sut->freeze();

        $this->expectException(InvalidWalletTransactionException::class);
        $this->expectErrorMessage('Wallet is frozen');

        $sut->withdraw(TransferRequestBuilder::of(100));
    }
}
