<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="cart_items")
 * @ORM\Entity(repositoryClass=CartItemRepository::class)
 */
class CartItem
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"cart.show"})
     */
    private $price;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true, "default"=0})
     *
     * @Groups({"cart.show"})
     */
    private $finalPrice;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"cart.show"})
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Inventory::class, inversedBy="cartItems", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"cart.show"})
     */
    private $inventory;

    /**
     * @ORM\ManyToOne(targetEntity=Cart::class, inversedBy="cartItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cart;

    /**
     * @var array<string>
     *
     * @Groups({"cart.show"})
     */
    private $messages = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubtotal(): ?int
    {
        return $this->subtotal;
    }

    public function setSubtotal(?int $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getGrandTotal(): ?int
    {
        return $this->grandTotal;
    }

    public function setGrandTotal(?int $grandTotal): self
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getFinalPrice(): ?int
    {
        return $this->finalPrice;
    }

    public function setFinalPrice(?int $price): self
    {
        $this->finalPrice = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

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

    public function priceHasBeenUpdated(): bool
    {
        return $this->getPrice() !== $this->getInventory()->getPrice();
    }

    public function getTitle(): ?string
    {
        return $this->getInventory()->getVariant()->getProduct()->getTitle();
    }

    public function getProductIsActive(): ?bool
    {
        return $this->getInventory()->getVariant()->getProduct()->getIsActive();
    }

    public function getProductStatus(): ?string
    {
        return $this->getInventory()->getVariant()->getProduct()->getStatus();
    }

    public function getCategoryCommissionFee(): ?float
    {
        return $this
            ->getInventory()
            ->getVariant()
            ->getProduct()
            ->getCategory()
            ->getCommission()
            ->getFee();
    }
}
