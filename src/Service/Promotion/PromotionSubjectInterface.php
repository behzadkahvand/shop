<?php

namespace App\Service\Promotion;

use App\Entity\CartItem;
use App\Entity\Customer;
use App\Entity\OrderItem;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Entity\PromotionDiscount;
use Doctrine\Common\Collections\Collection;

interface PromotionSubjectInterface
{
    public function getId();

    /**
     * @return Collection|Promotion[]
     *
     * @psalm-return Collection<array-key, PromotionInterface>
     */
    public function getPromotions(): Collection;

    public function hasPromotion(Promotion $promotion): bool;

    public function addPromotion(Promotion $promotion);

    public function removePromotion(Promotion $promotion);

    public function getPromotionSubjectTotal(): int;

    public function getPromotionCoupon(): ?PromotionCoupon;

    public function setPromotionCoupon(?PromotionCoupon $promotionCoupon): self;

    public function getItemsCount();

    /**
     * @return Collection|OrderItem[]|CartItem[]
     */
    public function getItems();

    /**
     * @return Collection|PromotionDiscount[]
     */
    public function getDiscounts(): Collection;

    public function addDiscount(PromotionDiscount $discount): self;

    public function removeDiscount(PromotionDiscount $discount): self;

    public function getCustomer(): ?Customer;

    public function updateTotals(): self;

    public function getAddress();
}
