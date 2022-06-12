<?php

namespace App\Tests\Unit\Service\Wallet;

use App\Dictionary\TransferReason;
use App\DTO\Wallet\TransferRequest;
use App\Entity\Customer;
use App\Entity\Wallet;
use App\Service\Wallet\ChangeWalletOwnerService;
use App\Tests\Unit\BaseUnitTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ChangeWalletOwnerServiceTest extends BaseUnitTestCase
{
    private ?ChangeWalletOwnerService $sut;
    private ?Customer $currentOwner;
    private ?Wallet $currentOwnerWallet;
    private ?Customer $newOwner;
    private ?Wallet $newOwnerWallet;

    protected function setUp(): void
    {
        $this->currentOwner = new Customer();
        $this->currentOwnerWallet = new Wallet();
        $this->currentOwner->setWallet($this->currentOwnerWallet);

        $this->newOwner = new Customer();
        $this->newOwnerWallet = new Wallet();
        $this->newOwner->setWallet($this->newOwnerWallet);

        $this->sut = new ChangeWalletOwnerService();
    }

    public function testShouldThrowExceptionIfWalletHasNotBalance(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('Current owner\'s wallet balance should be greater than zero');

        $this->sut->change($this->currentOwner, $this->newOwner);
    }

    public function testShouldThrowExceptionIfWalletHasMoreThanOneHistory(): void
    {
        $this->currentOwnerWallet->deposit($this->makeTransferRequest(1000));
        $this->currentOwnerWallet->deposit($this->makeTransferRequest(1500));

        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('Current owner\'s wallet should have only one transaction history');

        $this->sut->change($this->currentOwner, $this->newOwner);
    }

    public function testShouldThrowExceptionIfWalletTransactionReasonIsNotLendoCharge(): void
    {
        $this->currentOwnerWallet->deposit($this->makeTransferRequest(1000));

        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('Current owner\'s wallet transaction reason can only be lendo_charge');

        $this->sut->change($this->currentOwner, $this->newOwner);
    }

    public function testShouldThrowExceptionIfNewOwnerHasWalletBalance(): void
    {
        $this->currentOwnerWallet->deposit($this->makeTransferRequest(1000, TransferReason::LENDO_CHARGE));
        $this->newOwnerWallet->deposit($this->makeTransferRequest(100));

        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('New owner should not have wallet balance');

        $this->sut->change($this->currentOwner, $this->newOwner);
    }

    public function testShouldThrowExceptionIfNewOwnerHasWalletHistories(): void
    {
        $this->currentOwnerWallet->deposit($this->makeTransferRequest(1000, TransferReason::LENDO_CHARGE));
        $this->newOwnerWallet->deposit($this->makeTransferRequest(100));
        $this->newOwnerWallet->withdraw($this->makeTransferRequest(100));

        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('New owner should not have any wallet transaction histories');

        $this->sut->change($this->currentOwner, $this->newOwner);
    }

    public function testShouldChangeWalletOwnerIfEverythingIsCorrect(): void
    {
        $this->currentOwnerWallet->deposit($this->makeTransferRequest(1000, TransferReason::LENDO_CHARGE));

        $this->sut->change($this->currentOwner, $this->newOwner);

        self::assertNotSame($this->currentOwnerWallet, $this->currentOwner->getWallet());
        self::assertSame($this->currentOwnerWallet, $this->newOwner->getWallet());
        self::assertNotNull($this->currentOwner->getWallet());
        self::assertInstanceOf(Wallet::class, $this->currentOwner->getWallet());
    }

    private function makeTransferRequest(int $amount, string $reason = 'dummy reason'): TransferRequest
    {
        return new TransferRequest(
            $amount,
            $reason
        );
    }
}
