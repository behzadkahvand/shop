<?php

namespace App\DTO\Wallet;

use App\Entity\Order;

class TransferRequest
{
    public function __construct(
        protected int $amount,
        protected string $reason,
        protected ?string $referenceId = null,
        protected ?Order $order = null
    ) {
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
