<?php

namespace App\Service\Order\ReturnRequest\Transition;

class ReturnRequestTransition
{
    public const WAREHOUSE_CONFIRM = 'WAREHOUSE_CONFIRM';
    public const WAREHOUSE_RECEIVE = 'WAREHOUSE_RECEIVE';
    public const WAREHOUSE_EVALUATE = 'WAREHOUSE_EVALUATE';
    public const WAIT_FOR_REFUND = 'WAIT_FOR_REFUND';
    public const REFUND = 'REFUND';
    public const REJECT = 'REJECT';
    public const CANCEL = 'CANCEL';
}
