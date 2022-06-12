<?php

namespace App\Dictionary;

class OrderStatus extends Dictionary
{
    public const NEW = 'NEW';
    public const WAIT_CUSTOMER = 'WAIT_CUSTOMER';
    public const CALL_FAILED = 'CALL_FAILED';
    public const WAITING_FOR_PAY = 'WAITING_FOR_PAY';
    public const CONFIRMED = 'CONFIRMED';
    public const DELIVERED = 'DELIVERED';
    public const CANCELED = 'CANCELED';
    public const CANCELED_SYSTEM = 'CANCELED_SYSTEM';
    public const REFUND = 'REFUND';
    /*public const WAREHOUSE = 'WAREHOUSE';
    public const WAREHOUSE_PROCESSING = 'WAREHOUSE_PROCESSING';*/
}
