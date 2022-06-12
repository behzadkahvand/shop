<?php

namespace App\Service\Seller\SellerPackage\Status;

use App\Entity\SellerPackage;
use App\Service\Seller\SellerPackage\Status\Exceptions\CouldNotFindSellerPackageStatusException;

final class SellerPackageStatusFactory
{
    private iterable $statuses;

    public function __construct(iterable $statuses)
    {
        $this->statuses = $statuses;
    }

    public function create(SellerPackage $sellerPackage): AbstractSellerPackageStatus
    {
        /** @var AbstractSellerPackageStatus $status */
        foreach ($this->statuses as $status) {
            if ($status->support($sellerPackage)) {
                return $status;
            }
        }

        throw new CouldNotFindSellerPackageStatusException();
    }
}
