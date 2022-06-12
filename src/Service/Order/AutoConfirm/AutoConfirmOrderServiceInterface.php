<?php

namespace App\Service\Order\AutoConfirm;

use App\Entity\Order;

/**
 * Interface AutoConfirmOrderServiceInterface
 */
interface AutoConfirmOrderServiceInterface
{
    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isConfirmable(Order $order): bool;

    /**
     * @param Order $order
     */
    public function confirm(Order $order): void;
}
