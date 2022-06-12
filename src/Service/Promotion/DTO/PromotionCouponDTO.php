<?php

namespace App\Service\Promotion\DTO;

use App\Entity\Customer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class PromotionCouponDTO
{
    private ?string $code = null;

    private ?\DateTimeInterface $expiresAt = null;

    /**
     * @Assert\Type("integer")
     */
    private ?int $perCustomerUsageLimit = null;

    /**
     * @Assert\Type("integer")
     */
    private ?int $usageLimit = null;

    private Collection $customers;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): PromotionCouponDTO
    {
        $this->code = $code;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): PromotionCouponDTO
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getPerCustomerUsageLimit(): ?int
    {
        return $this->perCustomerUsageLimit;
    }

    public function setPerCustomerUsageLimit(?int $perCustomerUsageLimit): PromotionCouponDTO
    {
        $this->perCustomerUsageLimit = $perCustomerUsageLimit;
        return $this;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(?int $usageLimit): PromotionCouponDTO
    {
        $this->usageLimit = $usageLimit;
        return $this;
    }

    public function getCustomers()
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): PromotionCouponDTO
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): PromotionCouponDTO
    {
        if ($this->customers->contains($customer)) {
            $this->customers->remove($customer);
        }

        return $this;
    }

    /**
     * @param Customer[] $customers
     * @return PromotionCouponDTO
     */
    public function addCustomers(array $customers): self
    {
        foreach ($customers as $customer) {
            $this->addCustomer($customer);
        }

        return $this;
    }
}
