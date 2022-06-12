<?php

namespace App\Dictionary;

class SellerOrderStatus extends Dictionary
{
    public const NEW = 'RESERVED';
    public const WAITING = 'RESERVED';
    public const WAIT_CUSTOMER = 'RESERVED';
    public const CALL_FAILED = 'RESERVED';
    public const WAITING_FOR_PAY = 'RESERVED';

    public const CONFIRMED = 'CONFIRMED';

    public const DELIVERED = 'SENT';
    public const REFUND = 'SENT';

    public const CANCELED = 'CANCELED';
    public const CANCELED_SYSTEM = 'CANCELED';
}
