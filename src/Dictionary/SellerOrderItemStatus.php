<?php

namespace App\Dictionary;

class SellerOrderItemStatus extends Dictionary
{
    public const DELIVERED = 'DELIVERED';
    public const RECEIVED = 'RECEIVED';
    public const CANCELED_BY_USER = 'CANCELED_BY_USER';
    public const CANCELED_BY_SELLER = 'CANCELED_BY_SELLER';
    public const SENT_BY_SELLER = 'SENT_BY_SELLER';
    public const WAITING = 'WAITING';
    public const WAITING_FOR_SEND = 'WAITING_FOR_SEND';
    public const FULFILLING = 'FULFILLING';
    public const MISSED = 'MISSED';
    public const DAMAGED = 'DAMAGED';
    public const RETURNING = 'RETURNING';
    public const RETURNED = 'RETURNED';
    public const SENT_TO_CUSTOMER = 'SENT_TO_CUSTOMER';
    public const STORAGED = 'STORAGED';
}
