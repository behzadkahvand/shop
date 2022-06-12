<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;

/**
 * Class SellerOrderItemStatusFactory
 */
class SellerOrderItemStatusFactory
{
    private iterable $statuses;

    /**
     * SellerOrderItemStatusFactory constructor.
     *
     * @param AbstractSellerOrderItemStatus[]|iterable $statuses
     */
    public function __construct(iterable $statuses)
    {
        $this->statuses = $statuses;
    }

    /**
     * @param string $sellerOrderItemStatus
     *
     * @return AbstractSellerOrderItemStatus
     * @throws InvalidSellerOrderStatusException
     */
    public function create(string $sellerOrderItemStatus): AbstractSellerOrderItemStatus
    {
        foreach ($this->statuses as $status) {
            if ($status->support($sellerOrderItemStatus)) {
                return $status;
            }
        }

        throw new InvalidSellerOrderStatusException();
    }
}
