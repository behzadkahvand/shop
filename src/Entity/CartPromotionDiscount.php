<?php

namespace App\Entity;

use App\Repository\CartPromotionDiscountRepository;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CartPromotionDiscountRepository::class)
 */
class CartPromotionDiscount extends PromotionDiscount
{
    /**
     * @ORM\ManyToOne(targetEntity=Cart::class, inversedBy="discounts")
     * @ORM\JoinColumn(nullable=true, name="cart_id")
     */
    protected $subject;

    public function getSubjectType()
    {
        return "cart";
    }
}
