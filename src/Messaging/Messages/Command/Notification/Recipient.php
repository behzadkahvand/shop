<?php

namespace App\Messaging\Messages\Command\Notification;

class Recipient
{
    public function __construct(
        private string $mobile,
        private ?string $name = null,
        private ?string $userType = null,
        private ?string $userId = null
    ) {
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }
}
