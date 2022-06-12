<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\WishlistRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="wishlists")
 * @ORM\Entity(repositoryClass=WishlistRepository::class)
 * @UniqueEntity(
 *     fields={"customer","product"},
 *     message="The user has already added this product to wishlists.",
 *     groups={"customer.wishlist.add"}
 * )
 */
class Wishlist
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"default","wishlist.read", "wishlist.store",})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="wishlist")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default","wishlist.read", "wishlist.store",})
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
