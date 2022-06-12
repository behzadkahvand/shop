<?php

namespace App\Dictionary;

class SellerOrderItemStatusMappingDictionary extends Dictionary
{
    public const SENT_BY_SELLER = 'SENT';

    public const DELIVERED = 'DELIVERED';
    public const RECEIVED = 'DELIVERED';
    public const FULFILLING = 'DELIVERED';
    public const SENT_TO_CUSTOMER = 'DELIVERED';

    public const CANCELED_BY_USER = 'CANCELED';
    public const CANCELED_BY_SELLER = 'CANCELED';

    public const RETURNED = 'RETURNED';
    public const RETURNING = 'RETURNED';

    public const WAITING_FOR_SEND = 'WAITING_FOR_SEND';

    public const WAITING = 'RESERVED';

    public const STORAGED = 'DELIVERED';

    public static function getDefaultStatuses(): array
    {
        return array_diff_key(self::toArray(), [
            'WAITING_FOR_SEND' => true,
        ]);
    }

    /**
     * Unmapped statuses:
     *
     * public const SENT = 'SENT';
     * public const MISSED = 'MISSED';
     * public const DAMAGED = 'DAMAGED';
     */
}
