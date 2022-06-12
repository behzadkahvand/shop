<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PromotionRepository::class)
 */
class Promotion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"promotion.read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"promotion.create"})
     *
     * @Groups({"promotion.read"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"promotion.read"})
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(groups={"promotion.create"})
     *
     * @Groups({"promotion.read"})
     */
    private $priority = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"promotion.read"})
     */
    private $usageLimit;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\NotNull(groups={"promotion.create"})
     *
     * @Groups({"promotion.read"})
     */
    private $couponBased = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"promotion.read"})
     */
    private $startsAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"promotion.read"})
     */
    private $endsAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Groups({"promotion.read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Groups({"promotion.read"})
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=PromotionRule::class, mappedBy="promotion", orphanRemoval=true, cascade={"persist", "remove"})
     *
     * @Assert\Count(groups={"promotion.create"}, min=0)
     * @Assert\Valid(groups={"promotion.create"}, traverse=true)
     *
     * @Groups({"promotion.read"})
     */
    private $rules;

    /**
     * @ORM\OneToMany(targetEntity=PromotionAction::class, mappedBy="promotion", orphanRemoval=true, cascade={"persist", "remove"})
     *
     * @Assert\Count(groups={"promotion.create"}, min=1)
     * @Assert\Valid(groups={"promotion.create"}, traverse=true)
     *
     * @Groups({"promotion.read"})
     */
    private $actions;

    /**
     * @ORM\OneToMany(targetEntity=PromotionCoupon::class, mappedBy="promotion", orphanRemoval=true)
     */
    private $coupons;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     *
     * @Groups({"promotion.read"})
     */
    private $exclusive = true;

    /**
     * @ORM\Column(type="integer", options={"default"=0})
     *
     * @Groups({"promotion.read"})
     */
    private $used = 0;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({"promotion.read"})
     */
    private $enabled = false;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->coupons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

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

    public function getCouponBased(): ?bool
    {
        return $this->couponBased;
    }

    public function setCouponBased(?bool $couponBased): self
    {
        $this->couponBased = $couponBased;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeInterface $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|PromotionRule[]
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(PromotionRule $rule): self
    {
        if (!$this->rules->contains($rule)) {
            $this->rules[] = $rule;
            $rule->setPromotion($this);
        }

        return $this;
    }

    public function removeRule(PromotionRule $rule): self
    {
        if ($this->rules->removeElement($rule)) {
            // set the owning side to null (unless already changed)
            if ($rule->getPromotion() === $this) {
                $rule->setPromotion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PromotionAction[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(PromotionAction $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setPromotion($this);
        }

        return $this;
    }

    public function removeAction(PromotionAction $action): self
    {
        if ($this->actions->removeElement($action)) {
            // set the owning side to null (unless already changed)
            if ($action->getPromotion() === $this) {
                $action->setPromotion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PromotionCoupon[]
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    public function addCoupon(PromotionCoupon $coupon): self
    {
        if (!$this->coupons->contains($coupon)) {
            $this->coupons[] = $coupon;
            $coupon->setPromotion($this);
        }

        return $this;
    }

    public function removeCoupon(PromotionCoupon $coupon): self
    {
        if ($this->coupons->removeElement($coupon)) {
            // set the owning side to null (unless already changed)
            if ($coupon->getPromotion() === $this) {
                $coupon->setPromotion(null);
            }
        }

        return $this;
    }

    public function isExclusive(): ?bool
    {
        return $this->exclusive;
    }

    public function setExclusive(bool $exclusive): self
    {
        $this->exclusive = $exclusive;

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

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
