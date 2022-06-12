<?php

namespace App\Service\Seller\SellerOrderItemStatusLog;

use App\Entity\Admin;
use App\Entity\AdminUserSellerOrderItemStatusLog;
use App\Entity\CustomerUserSellerOrderItemStatusLog;
use App\Entity\Seller;
use App\Entity\SellerOrderItemStatusLog;
use App\Entity\SellerUserSellerOrderItemStatusLog;
use Symfony\Component\Security\Core\User\UserInterface;

class SellerOrderItemStatusLogFactory
{
    /**
     * @param UserInterface|null $user
     *
     * @return SellerOrderItemStatusLog|null
     */
    public function getSellerOrderItemStatusLog(?UserInterface $user)
    {
        if ($user instanceof Admin) {
            return (new AdminUserSellerOrderItemStatusLog())->setUser($user);
        }

        if ($user instanceof Seller) {
            return (new SellerUserSellerOrderItemStatusLog())->setUser($user);
        }

        return new CustomerUserSellerOrderItemStatusLog();
    }
}
