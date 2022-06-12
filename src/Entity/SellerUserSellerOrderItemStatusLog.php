<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SellerUserSellerOrderItemStatusLogRepository;

/**
 * @ORM\Entity(repositoryClass=SellerUserSellerOrderItemStatusLogRepository::class)
 */
class SellerUserSellerOrderItemStatusLog extends SellerOrderItemStatusLog
{
    /**
     * @ORM\ManyToOne(targetEntity=Seller::class)
     * @ORM\JoinColumn(nullable=true, name="user_id")
     */
    protected $user;
}
