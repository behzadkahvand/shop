<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\CartRepository;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity(repositoryClass=CartRepository::class)
 */
class Cart implements PromotionSubjectInterface
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="string", length=36)
     *
     * @Groups({"cart.show"})
     */
    private $id;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"cart.show"})
     */
    private $subtotal;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"cart.show"})
     */
    private $grandTotal;

    /**
     * @ORM\OneToOne(targetEntity=Customer::class, inversedBy="cart")
     *
     * @Groups({"cart.show"})
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity=CartItem::class, mappedBy="cart", orphanRemoval=true, cascade={"persist","remove"},
     *     fetch="EAGER")
     *
     * @Groups({"cart.show"})
     * @Assert\Count(min=1, groups={"order.store"}, minMessage="Cart has no cart item.")
     */
    private $cartItems;

    /**
     * @var array<string>
     *
     * @Groups({"cart.show"})
     */
    private $messages = [];

    /**
     * @ORM\ManyToMany(targetEntity=Promotion::class)
     */
    private $promotions;

    /**
     * @ORM\ManyToOne(targetEntity=PromotionCoupon::class)
     *
     * @Groups({"cart.show"})
     */
    private $promotionCoupon;

    /**
     * @ORM\OneToMany(targetEntity=CartPromotionDiscount::class, mappedBy="subject", orphanRemoval=true)
     *
     * @Groups({"cart.show"})
     */
    private $discounts;

    /**
     * @var CustomerAddress
     */
    private $address;

    /**
     * @ORM\OneToOne(targetEntity=AbandonedNotificationLog::class, mappedBy="cart", cascade={"persist", "remove"})
     */
    private $abandonedNotificationLog;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->discounts = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSubtotal(): ?int
    {
        return $this->subtotal;
    }

    public function setSubtotal(int $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getGrandTotal(): ?int
    {
        return $this->grandTotal;
    }

    public function setGrandTotal(int $grandTotal): self
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection|CartItem[]
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (! $this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setCart($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->contains($cartItem)) {
            $this->cartItems->removeElement($cartItem);
            // set the owning side to null (unless already changed)
            if ($cartItem->getCart() === $this) {
                $cartItem->setCart(null);
            }
        }

        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function addMessages(array $messages): self
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * @Groups({"cart.show"})
     */
    public function getItemsCount(): int
    {
        return collect($this->cartItems)->map(fn(CartItem $cartItem) => $cartItem->getQuantity())
                                        ->sum();
    }

    /**
     * @return Collection|Promotion[]
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): self
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions[] = $promotion;
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        $this->promotions->removeElement($promotion);

        return $this;
    }

    public function getPromotionCoupon(): ?PromotionCoupon
    {
        return $this->promotionCoupon;
    }

    public function setPromotionCoupon(?PromotionCoupon $promotionCoupon): self
    {
        $this->promotionCoupon = $promotionCoupon;

        return $this;
    }

    /**
     * @return Collection|CartPromotionDiscount[]
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function addDiscount(PromotionDiscount $discount): self
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts[] = $discount;
            $discount->setSubject($this);
        }

        return $this;
    }

    public function removeDiscount(PromotionDiscount $discount): self
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getSubject() === $this) {
                $discount->setSubject(null);
            }
        }

        return $this;
    }

    public function hasPromotion(Promotion $promotion): bool
    {
        return $this->getPromotions()->contains($promotion);
    }

    public function getPromotionSubjectTotal(): int
    {
        return $this->getGrandTotal();
    }

    public function updateTotals(): self
    {
        $grandTotal = 0;
        $subTotal = 0;
        foreach ($this->getCartItems() as $cartItem) {
            $grandTotal += $cartItem->getGrandTotal();
            $subTotal += $cartItem->getSubtotal();
        }

        foreach ($this->getDiscounts() as $discount) {
            $grandTotal -= $discount->getAmount();
        }

        $this->setGrandTotal($grandTotal);
        $this->setSubtotal($subTotal);

        return $this;
    }

    public function getItems()
    {
        return $this->getCartItems();
    }

    public function getAddress(): ?CustomerAddress
    {
        return $this->address;
    }

    public function setAddress(?CustomerAddress $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAbandonedNotificationLog(): ?AbandonedNotificationLog
    {
        return $this->abandonedNotificationLog;
    }

    public function setAbandonedNotificationLog($abandonedNotificationLog): self
    {
        $this->abandonedNotificationLog = $abandonedNotificationLog;
        return $this;
    }

    public function getItemsGrandTotal(): int
    {
        return collect($this->getCartItems())->sum(fn(CartItem $cartItem) => $cartItem->getGrandTotal());
    }
}
