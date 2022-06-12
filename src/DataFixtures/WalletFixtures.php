<?php

namespace App\DataFixtures;

use App\Entity\Wallet;

class WalletFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->createMany(
            Wallet::class,
            5,
            fn(Wallet $wallet, int $i) => $this->createZeroWallet(),
            true
        );

        $this->manager->flush();
    }

    private function createZeroWallet(): Wallet
    {
        return new Wallet();
    }
}
