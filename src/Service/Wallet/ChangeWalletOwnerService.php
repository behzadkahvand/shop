<?php

namespace App\Service\Wallet;

use App\Dictionary\TransferReason;
use App\Entity\Customer;
use App\Entity\Wallet;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ChangeWalletOwnerService
{
    public function change(Customer $currentOwner, Customer $newOwner): void
    {
        $this->validateCurrentOwner($currentOwner);
        $this->validateNewOwner($newOwner);

        $newOwner->setWallet($currentOwner->getWallet());
        $currentOwner->setWallet(new Wallet());
    }

    private function validateCurrentOwner(Customer $currentOwner): void
    {
        $wallet = $currentOwner->getWallet();
        $histories = $wallet->getHistories();

        if ($wallet->getBalance() === 0) {
            throw new BadRequestException('Current owner\'s wallet balance should be greater than zero');
        }

        if (count($histories) !== 1) {
            throw new BadRequestException('Current owner\'s wallet should have only one transaction history');
        }

        $history = $histories[0];

        if ($history->getReason() !== TransferReason::LENDO_CHARGE) {
            throw new BadRequestException('Current owner\'s wallet transaction reason can only be lendo_charge');
        }
    }

    private function validateNewOwner(Customer $newOwner): void
    {
        $wallet = $newOwner->getWallet();
        $histories = $wallet->getHistories();

        if ($wallet->getBalance() !== 0) {
            throw new BadRequestException('New owner should not have wallet balance');
        }

        if (count($histories) > 0) {
            throw new BadRequestException('New owner should not have any wallet transaction histories');
        }
    }
}
