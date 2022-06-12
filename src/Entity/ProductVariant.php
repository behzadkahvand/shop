<?php

namespace App\Entity;

use App\Dictionary\DefaultProductOptionCode;
use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\ProductVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="product_variants")
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 */
class ProductVariant
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "product.show",
     *     "variant.index",
     *     "variant.create",
     *     "variant.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "seller.order.items.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "seller.variant.create",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "order.items",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="relationField", value="product"),
     *          @Gedmo\SlugHandlerOption(name="relationSlugField", value="title"),
     *          @Gedmo\SlugHandlerOption(name="separator", value=""),
     *          @Gedmo\SlugHandlerOption(name="urilize", value=true)
     *      })
     * }, fields={"code"})
     * @Groups({
     *     "variant.index",
     *     "product.show",
     *     "variant.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "order.items",
     * })
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productVariants")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "order.show",
     *     "orderShipment.show",
     *     "variant.show",
     *     "cart.show",
     *     "inventories.index",
     *     "customer.order.show",
     *     "seller.order.items.index",
     *     "seller.variant.index",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "order.items",
     *     "carrier.inquiry.show",
     *     "return_request.show",
     *     "return_request.index"
     * })
     */
    private $product;

    /**
     * @ORM\OneToMany(targetEntity=Inventory::class, mappedBy="variant", orphanRemoval=true)
     *
     * @Groups({
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     * })
     */
    private $inventories;

    /**
     * @ORM\ManyToMany(targetEntity=ProductOptionValue::class, orphanRemoval=true, cascade={"remove"})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "variant.index",
     *     "variant.show",
     *     "inventories.show",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "admin.seller.order_items.index",
     * })
     */
    private $optionValues;

    /**
     * @var Inventory
     */
    private $minimumPriceInventory;

    public function __construct()
    {
        $this->inventories = new ArrayCollection();
        $this->optionValues = new ArrayCollection();
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
        $this->code = $code;

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

    /**
     * @return Collection|Inventory[]
     */
    public function getInventories(): Collection
    {
        return $this->inventories;
    }

    public function addInventory(Inventory $inventory): self
    {
        if (! $this->inventories->contains($inventory)) {
            $this->inventories[] = $inventory;
            $inventory->setVariant($this);
        }

        if (
            isset($this->minimumPriceInventory) &&
            $inventory->getFinalPrice() < $this->minimumPriceInventory->getFinalPrice()
        ) {
            $this->minimumPriceInventory = $inventory;
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): self
    {
        if ($this->inventories->contains($inventory)) {
            $this->inventories->removeElement($inventory);
            // set the owning side to null (unless already changed)
            if ($inventory->getVariant() === $this) {
                $inventory->setVariant(null);
            }
        }

        if ($inventory === $this->minimumPriceInventory) {
            unset($this->minimumPriceInventory);
        }

        return $this;
    }

    /**
     * @return Collection|ProductOptionValue[]
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function addOptionValue(ProductOptionValue $optionValue): self
    {
        if (! $this->optionValues->contains($optionValue)) {
            $this->optionValues[] = $optionValue;
        }

        return $this;
    }

    public function removeOptionValue(ProductOptionValue $optionValue): self
    {
        if ($this->optionValues->contains($optionValue)) {
            $this->optionValues->removeElement($optionValue);
        }

        return $this;
    }

    /**
     * @Assert\Callback(groups={"create", "update"})
     * @param ExecutionContextInterface $context
     * @param                             $payload
     */
    public function validateOptionValues(ExecutionContextInterface $context): void
    {
        $ids = $this->getOptionValues()->map(fn ($value) => $value->getOption()->getId())->toArray();
        $allowedIds = $this->getProduct()->getOptions()->map(fn ($option) => $option->getId())->toArray();

        if (count(array_diff($ids, $allowedIds)) > 0) {
            $context
                ->buildViolation('Option values does not match product options')
                ->atPath('optionValues')
                ->addViolation();
        }
    }

    /**
     * @SerializedName("inventory")
     */
    public function getMinimumPriceInventory(): ?Inventory
    {
        if (isset($this->minimumPriceInventory) && $this->minimumPriceInventory instanceof Inventory) {
            return $this->minimumPriceInventory;
        }

        $criteria = ProductVariantRepository::getMinimumPriceInventoryCriteria();
        $minimumPriceInventory = $this->inventories->matching($criteria)->first();

        if ($minimumPriceInventory === null || empty($minimumPriceInventory)) {
            return null;
        }

        return $this->minimumPriceInventory = $minimumPriceInventory;
    }

    /**
     * @Groups({"customer.order.show"})
     */
    public function getColor(): ?ProductOptionValue
    {
        return $this->getOptionValues()->filter(
            function (ProductOptionValue $value) {
                return $value->getOption()->getCode() === DefaultProductOptionCode::COLOR;
            }
        )->first() ?: null;
    }

    /**
     * @Groups({"customer.order.show"})
     */
    public function getGuaranty(): ?ProductOptionValue
    {
        return $this->getOptionValues()->filter(
            function (ProductOptionValue $value) {
                return DefaultProductOptionCode::GUARANTEE === $value->getOption()->getCode();
            }
        )->first() ?: null;
    }

    /**
     * @Groups({"customer.order.show"})
     *
     * @return ProductOptionValue[]
     */
    public function getOtherOptions(): Collection
    {
        $guaranteeAndColor = [
            DefaultProductOptionCode::GUARANTEE,
            DefaultProductOptionCode::COLOR,
        ];

        return $this->getOptionValues()->filter(
            function (ProductOptionValue $value) use ($guaranteeAndColor) {
                return false === in_array($value->getOption()->getCode(), $guaranteeAndColor);
            }
        );
    }

    /**
     * @Groups({"customer.order.show"})
     */
    public function getOtherOption(): ?ProductOptionValue
    {
        $guaranteeAndColor = [
            DefaultProductOptionCode::GUARANTEE,
            DefaultProductOptionCode::COLOR,
        ];
        return $this->getOptionValues()->filter(
            function (ProductOptionValue $value) use ($guaranteeAndColor) {
                return false === in_array($value->getOption()->getCode(), $guaranteeAndColor);
            }
        )->first() ?: null;
    }

    public function getPrimaryOptionValue()
    {
        $option = $this->getProduct()->getPrimaryOption();

        return collect($this->getOptionValues()->toArray())->first(
            fn (ProductOptionValue $pov): bool => $pov->getOption() === $option
        );
    }

    /**
     * @Groups({
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.order.items.index",
     *     "admin.seller.order.items.update_status"
     * })
     */
    public function getTitle(): string
    {
        $product = $this->getProduct();
        $productTitle = $product->getTitle();
        $optionValues = $this
            ->getOptionValues()
            ->map(fn (ProductOptionValue $pov) => $pov->getOption()->getName() . ': ' . $pov->getValue())
            ->getValues();

        return trim($productTitle . ' ' . implode(', ', $optionValues));
    }

    /**
     * @Groups({"seller.variant.index", "seller.productVariant.index"})
     */
    public function getMaxLead(): ?int
    {
        return $this->getProduct()?->getCategory()?->getMaxLeadTime();
    }

    /**
     * @Groups({"cart.show", "orderShipment.show"})
     * @SerializedName("optionValues")
     */
    public function getOptionValuesKeyByOptionCode(): ArrayCollection
    {
        $optionValues = collect($this->optionValues)
            ->keyBy(fn ($optionValue) => $optionValue->getOption()->getCode())
            ->toArray();

        return new ArrayCollection($optionValues);
    }

    public function getCategory(): Category
    {
        return $this->getProduct()->getCategory();
    }

    public function getBrand(): Brand
    {
        return $this->getProduct()->getBrand();
    }
}
