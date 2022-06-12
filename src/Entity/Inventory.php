<?php

namespace App\Entity;

use App\Dictionary\InventoryStatus;
use App\Dictionary\StockInventoryDictionary;
use App\Dictionary\WarehouseDictionary;
use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="inventories",
 *     indexes={
 *         @ORM\Index(columns={"final_price"}),
 *         @ORM\Index(columns={"is_active"}),
 *         @ORM\Index(columns={"lead_time"}),
 *         @ORM\Index(columns={"seller_stock"}),
 *         @ORM\Index(columns={"has_campaign"}),
 *     }
 * )
 *
 * @Table(name="inventories", uniqueConstraints={
 *     @UniqueConstraint(name="seller_variant", columns={"seller_id", "variant_id"})
 * })
 *
 * @ORM\Entity(repositoryClass=InventoryRepository::class)
 */
class Inventory
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "product.search",
     *     "customer.product.show",
     *     "cart.shipments",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "seller.order.items.index",
     *     "seller.variant.index",
     *     "seller.productVariant.index",
     *     "admin.seller.order_items.index",
     *     "product.search.seller.filter",
     *     "seller.package.show",
     *     "order.items",
     *     "customer.layout.onSaleBlocks",
     *     "return_request.show",
     *     "return_request.index"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "seller.order.items.index",
     *     "order.items",
     * })
     */
    private $status = InventoryStatus::WAIT_FOR_CONFIRM;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @SerializedName("stock")
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "seller.order.items.index",
     *     "order.items",
     * })
     */
    private $sellerStock;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "product.search",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $price;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "product.search",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $finalPrice;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "order.items",
     * })
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "cart.show",
     *     "order.items",
     * })
     */
    private $maxPurchasePerOrder;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @SerializedName("suppliesIn")
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.inventory.update",
     *     "product.search",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "customer.layout.onSaleBlocks",
     *     "admin.seller.order.items.index",
     * })
     */
    private $leadTime;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.show",
     *     "order.items",
     * })
     */
    private $orderCount = 0;

    /**
     * @ORM\ManyToOne(targetEntity=ProductVariant::class, inversedBy="inventories")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(groups={"inventories.store"})
     * @Assert\NotNull(groups={"inventories.store"})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "cart.show",
     *     "customer.order.show",
     *     "seller.order.items.index",
     *     "seller.inventory.update",
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
    private $variant;

    /**
     * @ORM\OneToMany(targetEntity=CartItem::class, mappedBy="inventory", orphanRemoval=true)
     */
    private $cartItems;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="inventory", orphanRemoval=true)
     */
    private $orderItems;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class, inversedBy="inventories", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(groups={"inventories.store", "inventories.update"})
     * @Assert\NotNull(groups={"inventories.store", "inventories.update"})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "order.show",
     *     "customer.product.show",
     *     "orderShipment.show",
     *     "product.search",
     *     "admin.seller.order_items.index",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "seller.order.items.index",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $seller;

    /**
     * @ORM\OneToMany(targetEntity=RateAndReview::class, mappedBy="inventory")
     */
    private $rateAndReviews;

    /**
     * @ORM\Column(type="integer", options={"default"=0,"unsigned"=true})
     *
     * @Assert\Type("int", groups={"inventories.store"})
     * @Assert\GreaterThanOrEqual(0, groups={"inventories.store"})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     * })
     */
    private $safeTime = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "seller.order.items.index",
     *     "seller.package.show",
     *     "seller.variant.index",
     *     "seller.productVariant.index",
     * })
     */
    private $sellerCode;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({
     *     "product.show",
     *     "product.search",
     *     "customer.product.show",
     *     "customer.layout.onSaleBlocks",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.update",
     * })
     */
    private $hasCampaign = false;

    public function __construct()
    {
        $this->cartItems      = new ArrayCollection();
        $this->orderItems     = new ArrayCollection();
        $this->rateAndReviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSellerStock(): ?int
    {
        return $this->sellerStock;
    }

    public function setSellerStock(?int $sellerStock): self
    {
        $this->sellerStock = $sellerStock;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    public function decreaseStockCount(int $step = 1): self
    {
        if (0 < $this->getSellerStock() && 1 === $step) {
            $this->sellerStock--;

            return $this;
        }

        $this->sellerStock -= min($this->getSellerStock(), (int) abs($step));

        return $this;
    }

    public function increaseStockCount(int $step = 1): self
    {
        $this->sellerStock += (int) abs($step);

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

    public function setFinalPrice(?int $finalPrice): self
    {
        $this->finalPrice = $finalPrice;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): Inventory
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getMaxPurchasePerOrder(): ?int
    {
        return $this->maxPurchasePerOrder;
    }

    public function setMaxPurchasePerOrder(?int $maxPurchasePerOrder): self
    {
        $this->maxPurchasePerOrder = $maxPurchasePerOrder;

        return $this;
    }

    public function getLeadTime(): ?int
    {
        return $this->leadTime;
    }

    public function setLeadTime(?int $leadTime): self
    {
        $this->leadTime = $leadTime;

        return $this;
    }

    public function getOrderCount(): ?int
    {
        return $this->orderCount;
    }

    public function setOrderCount(?int $orderCount): self
    {
        $this->orderCount = $orderCount;

        return $this;
    }

    public function getVariant(): ?ProductVariant
    {
        return $this->variant;
    }

    public function setVariant(?ProductVariant $variant): self
    {
        $this->variant = $variant;

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
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setInventory($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->contains($cartItem)) {
            $this->cartItems->removeElement($cartItem);
            // set the owning side to null (unless already changed)
            if ($cartItem->getInventory() === $this) {
                $cartItem->setInventory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setInventory($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
            // set the owning side to null (unless already changed)
            if ($orderItem->getInventory() === $this) {
                $orderItem->setInventory(null);
            }
        }

        return $this;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * @Groups({"customer.product.show"})
     */
    public function getWarehouse(): string
    {
        return $this->leadTime === 0 ? WarehouseDictionary::TIMCHEH : WarehouseDictionary::SELLER;
    }

    /**
     * @Groups({
     *     "customer.product.show",
     *     "inventories.index",
     *     "order.show",
     *     "orderShipment.show",
     *     "order.items",
     * })
     */
    public function getTitle(): string
    {
        return $this->getVariant()->getTitle();
    }

    public function getProductIsActive(): ?bool
    {
        return $this->getVariant()->getProduct()->getIsActive();
    }

    public function getProductStatus(): ?string
    {
        return $this->getVariant()->getProduct()->getStatus();
    }

    public function incrementOrderCount(): self
    {
        $this->orderCount++;

        return $this;
    }

    /**
     * @SerializedName("options")
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     * })
     *
     * @OA\Property(
     *     type="object",
     *     @OA\Property(
     *         property="color",
     *         type="object",
     *         ref=@Model(type=ProductOptionValue::class, groups={"inventories.index","inventories.show",
     *     "inventories.store", "inventories.update"})
     *     ),
     *     @OA\Property(
     *         property="guarantee",
     *         type="string",
     *         ref=@Model(type=ProductOptionValue::class, groups={"inventories.index","inventories.show",
     *     "inventories.store", "inventories.update"})
     *     ),
     *     @OA\Property(
     *         property="otherOptions",
     *         type="array",
     *         @OA\Items(ref=@Model(type=ProductOptionValue::class, groups={"inventories.index","inventories.show",
     *     "inventories.store", "inventories.update"}))
     *     ),
     * )
     */
    public function getOptions(): array
    {
        $variant      = $this->getVariant();
        $color        = $variant->getColor();
        $guarantee    = $variant->getGuaranty();
        $otherOptions = $variant->getOtherOptions();
        $otherOption  = $variant->getOtherOption();

        return compact('color', 'guarantee', 'otherOption', 'otherOptions');
    }

    /**
     * @SerializedName("cashback")
     *
     * @Groups({
     *     "customer.product.show",
     *     "product.search",
     *     "product.search.seller.filter",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    public function getCashback(): int
    {
        $commission = $this->getVariant()?->getProduct()?->getCategory()?->getCommission();

        return $this->calculateCashback($commission);
    }

    public function isConfirmed(): bool
    {
        return $this->status === InventoryStatus::CONFIRMED;
    }

    public function isWaitingForConfirm(): bool
    {
        return $this->status === InventoryStatus::WAIT_FOR_CONFIRM;
    }

    protected function calculateCashback(?float $commission): int
    {
        $share      = (25 / 100);
        $margin     = (($commission ?: 0) / 100);
        $finalPrice = $this->getFinalPrice();
        $cost       = 10_000;

        $cashback                 = $share * (($margin * $finalPrice) - $cost);
        $maximumAllowedCashback   = 500_000;
        $twoPercentOfProductPrice = (2 / 100) * $finalPrice;

        $min = min($cashback, $maximumAllowedCashback, $twoPercentOfProductPrice);

        if ($min < 0) {
            return 0;
        }

        $step       = 500;
        $roundedMin = $min;

        if ($min % $step > 0) {
            $roundedMin = is_even($min / $step)
                ? floor($min / $step) * $step
                : ceil($min / $step) * $step;
        }

        if (($min % $step === 0) && is_odd($min / $step)) {
            $roundedMin = $min - $step;
        }

        return $roundedMin;
    }

    /**
     * @return Collection|RateAndReview[]
     */
    public function getRateAndReviews(): Collection
    {
        return $this->rateAndReviews;
    }

    public function addRateAndReview(RateAndReview $rateAndReview): self
    {
        if (!$this->rateAndReviews->contains($rateAndReview)) {
            $this->rateAndReviews[] = $rateAndReview;
            $rateAndReview->setInventory($this);
        }

        return $this;
    }

    public function removeRateAndReview(RateAndReview $rateAndReview): self
    {
        if ($this->rateAndReviews->removeElement($rateAndReview)) {
            // set the owning side to null (unless already changed)
            if ($rateAndReview->getInventory() === $this) {
                $rateAndReview->setInventory(null);
            }
        }

        return $this;
    }

    public function getSafeTime(): ?int
    {
        return $this->safeTime;
    }

    public function setSafeTime(int $safeTime): self
    {
        $this->safeTime = $safeTime;

        return $this;
    }

    public function getSellerCode(): ?string
    {
        return $this->sellerCode;
    }

    public function setSellerCode(?string $sellerCode): self
    {
        $this->sellerCode = $sellerCode;

        return $this;
    }

    public function getSafeSuppliesIn(): int
    {
        return (int) $this->getLeadTime() + $this->getSafeTime();
    }

    public function isAvailable(): bool
    {
        return $this->getIsActive() && $this->isConfirmed() && $this->getSellerStock() > 0;
    }

    public function getAllInventoryChangeableProperties(): array
    {
        return [
            'status',
            'sellerStock',
            'price',
            'finalPrice',
            'isActive',
            'maxPurchasePerOrder',
            'leadTime',
            'safeTime',
        ];
    }

    public function getCategory(): Category
    {
        return $this->getVariant()->getCategory();
    }

    public function getBrand(): Brand
    {
        return $this->getVariant()->getBrand();
    }

    public function getHasCampaign(): ?bool
    {
        return $this->hasCampaign;
    }

    public function setHasCampaign(bool $hasCampaign): self
    {
        $this->hasCampaign = $hasCampaign;

        return $this;
    }

    public function isBelongTo(Seller $seller): bool
    {
        return $this->getSeller()->getId() === $seller->getId();
    }

    public function getProduct(): Product
    {
        return $this->getVariant()->getProduct();
    }

    public function isBuyBox(): bool
    {
        return $this->getProduct()->getBuyBox()?->getId() === $this->getId();
    }

    /**
     * @Groups({
     *     "customer.layout.onSaleBlocks"
     * })
     */
    public function getStockInventory(): string
    {
        if ($this->getIsActive() && $this->isConfirmed() && $this->getSellerStock() > 0) {
            return StockInventoryDictionary::IS_STOCK;
        }

        return StockInventoryDictionary::OUT_OF_STOCK;
    }
}
