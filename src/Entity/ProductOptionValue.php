<?php

namespace App\Entity;

use App\Repository\ProductOptionValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="product_option_values")
 * @ORM\Entity(repositoryClass=ProductOptionValueRepository::class)
 */
class ProductOptionValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "product.option",
     *     "variant.index",
     *     "product.show",
     *     "variant.show",
     *     "order.show",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "customer.product.show",
     *     "seller.variant.index",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "orderShipment.shipmentPrint",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"admin.create", "admin.update", "variant.show"})
     *
     * @Groups({
     *     "default",
     *     "product.option",
     *     "variant.index",
     *     "product.show",
     *     "order.show",
     *     "cart.show",
     *     "orderShipment.show",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "product.search",
     *     "customer.order.show",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "admin.seller.order_items.index",
     *     "customer.product.rateAndReview.index",
     *     "product.search.seller.filter",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "orderShipment.shipmentPrint",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "customer.layout.onSaleBlocks",
     *     "return_request.show"
     * })
     */
    private $value;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Slug(fields={"value"})
     *
     * @Groups({
     *     "default",
     *     "inventories.grid",
     *     "product.option",
     *     "product.show",
     *     "variant.show",
     *     "order.show",
     *     "inventories.show",
     *     "inventories.index",
     *     "inventories.store",
     *     "inventories.update",
     *     "product.search",
     *     "customer.product.show",
     *     "cart.show",
     *     "orderShipment.show",
     *     "cart.shipments",
     *     "seller.variant.index",
     *     "product.search.seller.filter",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOption::class, inversedBy="values")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "inventories.grid",
     *     "variant.index",
     *     "variant.show",
     *     "order.show",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "customer.product.show",
     *     "seller.variant.index",
     *     "admin.seller.order_items.index",
     *     "return_request.show"
     * })
     */
    private $option;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     *
     * @Groups({
     *     "default",
     *     "inventories.grid",
     *     "product.option",
     *     "product.show",
     *     "order.show",
     *     "cart.show",
     *     "orderShipment.show",
     *     "product.search",
     *     "customer.product.show",
     *     "customer.order.show",
     *     "seller.variant.index",
     *     "product.search.seller.filter",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $attributes = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
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

    public function getOption(): ?ProductOption
    {
        return $this->option;
    }

    public function setOption(?ProductOption $option): self
    {
        $this->option = $option;

        return $this;
    }
}
