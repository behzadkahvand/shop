<?php

namespace App\Service\Promotion\Builders;

use App\Entity\Customer;
use App\Service\Promotion\DTO\PromotionCouponDTO;

class PromotionCouponDTOBuilder
{
    /**
     * @var Customer[] $customers
     */
    private array $customers;

    public function withCustomers(array $customers): self
    {
        $this->customers = $customers;

        return $this;
    }

    public function build(): PromotionCouponDTO
    {
        $couponDTO = new PromotionCouponDTO();

        if (isset($this->customers)) {
            $couponDTO->addCustomers($this->customers);
        }

        return $couponDTO;
    }
}
