<?php

namespace App\Entity;

use App\Repository\PromotionCouponRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PromotionCouponRepository::class)
 *
 * @UniqueEntity(fields={"code"}, groups={"promotionCoupon.create"})
 */
class PromotionCoupon
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"promotionCoupon.create"})
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=Promotion::class, inversedBy="coupons")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"promotionCoupon.details"})
     */
    private $promotion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\NotBlank(groups={"promotionCoupon.create"})
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $used = 0;

    /**
     * @ORM\ManyToMany(targetEntity=Customer::class, inversedBy="promotionCoupons")
     *
     * @Groups({"promotionCoupon.details"})
     */
    private $customers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Type("integer")
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $perCustomerUsageLimit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Type("integer")
     *
     * @Groups({"promotionCoupon.read"})
     */
    private $usageLimit;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = strtolower($code);

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUsed(): ?int
    {
        return $this->used;
    }

    public function setUsed(int $used): self
    {
        $this->used = $used;

        return $this;
    }

    /**
     * @return Collection|Customer[]
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        $this->customers->removeElement($customer);

        return $this;
    }

    public function containsCustomer(Customer $customer)
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('id', $customer->getId()));
        $criteria->setMaxResults(1);

        $matched = $this->getCustomers()->matching($criteria);

        return $matched->count() > 0;
    }

    public function getPerCustomerUsageLimit(): ?int
    {
        return $this->perCustomerUsageLimit;
    }

    public function setPerCustomerUsageLimit(?int $perCustomerUsageLimit): self
    {
        $this->perCustomerUsageLimit = $perCustomerUsageLimit;

        return $this;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(?int $usageLimit): self
    {
        $this->usageLimit = $usageLimit;

        return $this;
    }
}
