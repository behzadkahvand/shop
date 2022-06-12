<?php

namespace App\Messaging\Messages\Command\Wallet;

final class CreateWalletForUser
{
    public function __construct(private int $userId)
    {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
