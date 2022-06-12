<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\ShippingCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="shipping_categories")
 * @ORM\Entity(repositoryClass=ShippingCategoryRepository::class)
 * @UniqueEntity(fields={"name"}, groups={"shipping.category.create", "shipping.category.update"})
 */
class ShippingCategory
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "product.index",
     *     "product.show",
     *     "shipping-methods.grid",
     *     "customer.product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "variant.show",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"shipping.category.create", "shipping.category.update"})
     * @Groups({
     *     "default",
     *     "product.index",
     *     "product.show",
     *     "shipping-methods.grid",
     *     "customer.product.show",
     *     "orderShipment.index",
     *     "customer.order.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "variant.show",
     * })
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=ShippingMethod::class, inversedBy="categories", cascade={"persist"})
     */
    private $methods;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="shippingCategory")
     */
    private $products;

    /**
     * @ORM\OneToOne(targetEntity=Delivery::class, inversedBy="shippingCategory", cascade={"persist", "remove"})
     */
    private $delivery;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "orderShipment.show",
     * })
     */
    private $title;

    public function __construct()
    {
        $this->methods = new ArrayCollection();
        $this->products = new ArrayCollection();
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

    /**
     * @return Collection|ShippingMethod[]
     */
    public function getMethods(): Collection
    {
        return $this->methods;
    }

    public function addMethod(ShippingMethod $method): self
    {
        if (!$this->methods->contains($method)) {
            $this->methods[] = $method;
        }

        return $this;
    }

    public function removeMethod(ShippingMethod $method): self
    {
        if ($this->methods->contains($method)) {
            $this->methods->removeElement($method);
        }

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(Delivery $delivery): self
    {
        $this->delivery = $delivery;

        // set the owning side of the relation if necessary
        if ($delivery->getShippingCategory() !== $this) {
            $delivery->setShippingCategory($this);
        }

        return $this;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setShippingCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getShippingCategory() === $this) {
                $product->setShippingCategory(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
