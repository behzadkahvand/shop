<?php

namespace App\Tests\Controller\Traits;

use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\PromotionCoupon;
use App\Entity\PromotionRule;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;

trait PromotionTrait
{
    public function updatePromotionRuleConfigurationForCustomer(
        PromotionCoupon $coupon,
        Customer $customer,
        CustomerAddress $address = null
    ) {
        $promotion = $coupon->getPromotion();
        /** @var PromotionRule $categoryRule */
        $categoryRule = $promotion->getRules()->filter(fn(PromotionRule $rule) => $rule->getType() === 'category')->first();
        $categoryRule->setConfiguration([
            'category_ids' => [
                $this->getService(CategoryRepository::class)->findOneBy(['title' => 'television'])->getId(),
                $this->getService(CategoryRepository::class)->findOneBy(['title' => 'kitchen'])->getId(),
            ]
        ]);

        /** @var PromotionRule $productRule */
        $productRule = $promotion->getRules()->filter(fn(PromotionRule $rule) => $rule->getType() === 'product')->first();
        $productRule->setConfiguration([
            'product_ids' => [
                $customer->getCart()->getItems()->first()->getInventory()->getVariant()->getProduct()->getId(),
            ]
        ]);

        /** @var PromotionRule $productRule */
        $cityRule = $promotion->getRules()->filter(fn(PromotionRule $rule) => $rule->getType() === 'city')->first();
        $cityRule->setConfiguration([
            'city_ids' => [
                $address ? $address->getCity()->getId() :
                    $this->getService(CityRepository::class)->findOneBy(['name' => 'تهران'])->getId(),
            ]
        ]);

        $this->manager->flush();
    }
}
