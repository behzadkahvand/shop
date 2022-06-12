<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdminUserSellerOrderItemStatusLogRepository;

/**
 * @ORM\Entity(repositoryClass=AdminUserSellerOrderItemStatusLogRepository::class)
 */
class CustomerUserSellerOrderItemStatusLog extends SellerOrderItemStatusLog
{
    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     * @ORM\JoinColumn(nullable=true, name="user_id")
     */
    protected $user;
}
