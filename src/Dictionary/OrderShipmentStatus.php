<?php

namespace App\Dictionary;

class OrderShipmentStatus extends Dictionary
{
    public const NEW = 'NEW';
    public const WAITING_FOR_SUPPLY = 'WAITING_FOR_SUPPLY';
    public const PREPARING = 'PREPARING';
    public const PREPARED = 'PREPARED';
    public const PACKAGED = 'PACKAGED';
    public const WAITING_FOR_SEND = 'WAITING_FOR_SEND';
    public const SENT = 'SENT';
    public const DELIVERED = 'DELIVERED';
    public const CANCELED = 'CANCELED';
    public const AFTER_SALES = 'AFTER_SALES';
    public const RETURNING = 'RETURNING';
    public const RETURNED = 'RETURNED';
    public const THIRD_PARTY_LOGISTICS = 'THIRD_PARTY_LOGISTICS';
    public const WAREHOUSE = 'WAREHOUSE';
    public const CUSTOMER_ABSENCE = 'CUSTOMER_ABSENCE';
    public const CANCELED_BY_CUSTOMER = 'CANCELED_BY_CUSTOMER';
    public const NO_SEND = 'NO_SEND';
}
