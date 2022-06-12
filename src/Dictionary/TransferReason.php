<?php

namespace App\Dictionary;

class TransferReason extends Dictionary
{
    public const LENDO_CHARGE = 'LENDO_CHARGE';
    public const LENDO_DISCHARGE = 'LENDO_DISCHARGE';
    public const ORDER_PURCHASE = 'ORDER_PURCHASE';
    public const ORDER_CANCELED = 'ORDER_CANCELED';
    public const ORDER_CANCELED_BY_SYSTEM = 'ORDER_CANCELED_BY_SYSTEM';
    public const ORDER_REFUND = 'ORDER_REFUND';
    public const ORDER_SHIPMENT_UPDATE = 'ORDER_SHIPMENT_UPDATE';
    public const UPDATE_ORDER_ITEM = 'UPDATE_ORDER_ITEM';
}
