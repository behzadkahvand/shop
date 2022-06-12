<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\CodeAwareInterface;
use App\Entity\Common\Timestampable;
use App\Repository\ProductOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="product_options")
 * @ORM\Entity(repositoryClass=ProductOptionRepository::class)
 */
class ProductOption implements CodeAwareInterface
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "product.option",
     *     "variant.index",
     *     "product.index",
     *     "product.show",
     *     "variant.show",
     *     "inventories.show",
     *     "inventories.index",
     *     "inventories.store",
     *     "inventories.update",
     *     "customer.product.show",
     *     "seller.variant.index",
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"name"})
     * @Assert\NotBlank(groups={"product.option.create", "product.option.update"})
     * @Groups({
     *     "product.option",
     *     "product.index",
     *     "product.show",
     *     "inventories.show",
     *     "customer.product.show",
     *     "seller.variant.index",
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     * })
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"product.option.create", "product.option.update"})
     *
     * @Groups({
     *     "product.option",
     *     "product.index",
     *     "variant.index",
     *     "product.show",
     *     "variant.show",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "customer.order.show",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "return_request.show"
     * })
     */
    private $name;

    /**
     * @ORM\OneToMany(
     *     targetEntity=ProductOptionValue::class,
     *     mappedBy="option",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     * @Groups({"product.option",
     *     "product.show", "customer.order.show"})
     */
    private $values;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, mappedBy="options")
     */
    private $products;

    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
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
     * @return Collection|ProductOptionValue[]
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(ProductOptionValue $value): self
    {
        if (! $this->values->contains($value)) {
            $this->values[] = $value;
            $value->setOption($this);
        }

        return $this;
    }

    public function removeValue(ProductOptionValue $value): self
    {
        if ($this->values->contains($value)) {
            $this->values->removeElement($value);
            // set the owning side to null (unless already changed)
            if ($value->getOption() === $this) {
                $value->setOption(null);
            }
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

    public function addProduct(Product $product): self
    {
        if (! $this->products->contains($product)) {
            $this->products[] = $product;
            $product->addOption($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            $product->removeOption($this);
        }

        return $this;
    }
}
