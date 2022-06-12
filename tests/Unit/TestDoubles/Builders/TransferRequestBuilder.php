<?php

namespace App\Tests\Unit\TestDoubles\Builders;

use App\DTO\Wallet\TransferRequest;

class TransferRequestBuilder
{
    public static function of(int $amount, string $reason = null): TransferRequest
    {
        return new TransferRequest($amount, $reason ?? 'dummy reason');
    }
}
