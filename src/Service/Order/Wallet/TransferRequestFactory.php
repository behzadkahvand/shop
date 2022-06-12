<?php

namespace App\Service\Order\Wallet;

use App\DTO\Wallet\TransferRequest;
use App\Entity\Order;

class TransferRequestFactory
{
    public function make(int $amount, string $reason, ?string $referenceId = null, ?Order $order = null): TransferRequest
    {
        return new TransferRequest(
            $amount,
            $reason,
            $referenceId,
            $order
        );
    }
}
