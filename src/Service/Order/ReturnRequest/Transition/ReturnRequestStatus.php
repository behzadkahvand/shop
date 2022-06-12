<?php

namespace App\Service\Order\ReturnRequest\Transition;

class ReturnRequestStatus
{
    public const APPROVED = 'APPROVED';
    public const RETURNING = 'RETURNING';
    public const RETURNED = 'RETURNED';
    public const WAITING_REFUND = 'WAITING_REFUND';
    public const REFUNDED = 'REFUNDED';
    public const REJECTED = 'REJECTED';
    public const CANCELED = 'CANCELED';

    /**
     * @return array<string, int>
     */
    public static function sorted(): array
    {
        return [
            ReturnRequestStatus::APPROVED => 0,
            ReturnRequestStatus::RETURNING => 1,
            ReturnRequestStatus::RETURNED => 2,
            ReturnRequestStatus::WAITING_REFUND => 3,
            ReturnRequestStatus::REFUNDED => 4,
        ];
    }

    public static function indexOf(string $status): int
    {
        return self::sorted()[$status];
    }
}
