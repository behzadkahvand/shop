<?php

namespace App\Entity;

use App\Entity\Common\HasInventoryTrait;
use App\Repository\OrderItemRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity(repositoryClass=OrderItemRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class OrderItem
{
    use SoftDeleteableEntity;
    use HasInventoryTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "order.index",
     *     "order.show",
     *     "order.items",
     *     "orderShipment.show",
     *     "seller.order.items.index",
     *     "return_request.show",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @Groups({
     *     "default",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "order.items",
     *     "orderItem.shipmentPrint",
     * })
     */
    private $subtotal;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @Groups({
     *     "default",
     *     "order.show",
     *     "orderShipment.show",
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "customer.order.show",
     *     "admin.seller.order_items.index",
     *     "order.items",
     *     "orderItem.shipmentPrint",
     * })
     */
    private $grandTotal;

    /**
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "order.show",
     *     "orderShipment.show",
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "customer.order.show",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "order.items",
     *     "carrier.inquiry.show",
     *     "orderItem.shipmentPrint",
     *     "return_request.show",
     * })
     */
    private $quantity;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({
     *     "default",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "order.items",
     *     "orderItem.shipmentPrint",
     * })
     */
    private $price;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true, "default"=0})
     *
     * @Groups({
     *     "default",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "order.items",
     *     "orderItem.shipmentPrint",
     * })
     */
    private $finalPrice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @SerializedName("suppliesIn")
     * @Groups({
     *     "default",
     *     "order.index",
     *     "order.show",
     *     "order.items",
     *     "orderShipment.show",
     *     "orderItem.shipmentPrint",
     *     "return_request.show",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order_items.index",
     * })
     */
    private $leadTime = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $commission;

    /**
     * @ORM\ManyToOne(targetEntity=Inventory::class, inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "seller.order.items.index",
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
    private $inventory;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order_items.index",
     * })
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity=OrderShipment::class, inversedBy="orderItems")
     *
     * @Groups({
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "seller.order.items.index",
     *     "admin.seller.order.items.index",
     * })
     */
    private $orderShipment;

    /**
     * @ORM\OneToOne(targetEntity=SellerOrderItem::class, mappedBy="orderItem", cascade={"persist", "remove"})
     *
     * @Groups({"default", "order.show", "orderShipment.show", "order.items", "return_request.show"})
     */
    private $sellerOrderItem;

    /**
     * @ORM\OneToMany(targetEntity=OrderItemLog::class, mappedBy="orderItem", orphanRemoval=true)
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity=OrderPromotionDiscount::class, mappedBy="orderItem")
     *
     * @Groups({
     *     "order.items",
     * })
     */
    private $discounts;

    /**
     * @ORM\OneToMany (targetEntity=ReturnRequestItem::class, mappedBy="orderItem")
     *
     */
    private $returnRequestItems;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     */
    private $deletedBy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Expression(
     *     "this.getInventory() && this.getInventory().getSeller() && this.getInventory().getSeller().getIsRetail() == 1",
     *     message="The ordered item must be for a retailer!",
     *     groups={"admin.order.item.update.retail_price"}
     * )
     *
     * @Groups({
     *     "order.show",
     *     "orderShipment.show"
     * })
     */
    private $retailPrice;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     */
    private $retailPriceUpdatedBy;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->discounts = new ArrayCollection();
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function priceHasBeenUpdated(): bool
    {
        return $this->getPrice() !== $this->getInventory()->getPrice();
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getFinalPrice(): ?int
    {
        return $this->finalPrice;
    }

    public function setFinalPrice(int $price): self
    {
        $this->finalPrice = $price;

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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return OrderShipment|null
     */
    public function getOrderShipment(): ?OrderShipment
    {
        return $this->orderShipment;
    }

    /**
     * @param mixed $orderShipment
     *
     * @return OrderItem
     */
    public function setOrderShipment($orderShipment): OrderItem
    {
        $this->orderShipment = $orderShipment;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(?float $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    public function getSellerOrderItem(): ?SellerOrderItem
    {
        return $this->sellerOrderItem;
    }

    public function setSellerOrderItem(SellerOrderItem $sellerOrderItem): self
    {
        $this->sellerOrderItem = $sellerOrderItem;

        return $this;
    }

    public function isSent(): bool
    {
        return $this->getSellerOrderItem()->isSent();
    }

    /**
     * @return Collection|OrderItemLog[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(OrderItemLog $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setOrderItem($this);
        }

        return $this;
    }

    public function removeLog(OrderItemLog $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getOrderItem() === $this) {
                $log->setOrderItem(null);
            }
        }

        return $this;
    }

    public function releaseReservedStock(): void
    {
        $newStock = $this->getInventory()->getSellerStock() + $this->quantity;

        $this->getInventory()->setSellerStock($newStock);
    }

    /**
     * @return Collection|OrderPromotionDiscount[]
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function addDiscount(OrderPromotionDiscount $discount): self
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts[] = $discount;
            $discount->setOrderItem($this);
        }

        return $this;
    }

    public function removeDiscount(OrderPromotionDiscount $discount): self
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getOrderItem() === $this) {
                $discount->setOrderItem(null);
            }
        }

        return $this;
    }

    /**
     * @SerializedName("sellingPrice")
     *
     * @Groups({
     *     "carrier.inquiry.show",
     * })
     */
    public function getSellingPrice(): int
    {
        return $this->grandTotal;
    }

    public function getDiscountAmount(): int
    {
        $discountAmount = 0;

        foreach ($this->getDiscounts() as $discount) {
            $discountAmount += $discount->getAmount();
        }

        return $discountAmount;
    }

    public function getCategory(): Category
    {
        return $this->getInventory()->getCategory();
    }

    public function getBrand(): Brand
    {
        return $this->getInventory()->getBrand();
    }

    /**
     * @return ?ReturnRequestItem[]
     */
    public function getReturnRequestItems(): ?Collection
    {
        return $this->returnRequestItems;
    }

    /**
     * @return ?ReturnRequestItem[]
     */
    public function getRefundedReturnRequestItems(): ?Collection
    {
        return $this->getReturnRequestItems()->filter(
            fn(ReturnRequestItem $item): bool => $item->isRefunded()
        );
    }

    public function isBelongTo(Order $order): bool
    {
        return $this->getOrder() === $order;
    }

    public function getReturnedItemsRefundAmount(): int
    {
        $sum = 0;
        foreach ($this->getRefundedReturnRequestItems() as $item) {
            $sum += $item->getRefundAmount();
        }

        return $sum;
    }

    public function isDelivered(): bool
    {
        return $this->getSellerOrderItem()->isDelivered();
    }

    public function getReturnItemsCount(): int
    {
        $items = $this->getReturnRequestItems()->filter(
            fn(ReturnRequestItem $item): bool => !$item->isCanceled() && !$item->isRejected()
        );

        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getQuantity();
        }

        return $sum;
    }

    /**
     * @Groups({
     *     "order.show",
     *     "orderShipment.show"
     * })
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function getDeletedBy(): ?Admin
    {
        return $this->deletedBy;
    }

    public function setDeletedBy($deletedBy): self
    {
        $this->deletedBy = $deletedBy;
        return $this;
    }

    /**
     * @Groups({
     *     "order.show",
     *     "orderShipment.show"
     * })
     * @SerializedName("deletedBy")
     */
    public function getDeletedByFullName(): ?string
    {
        return $this->getDeletedBy()?->getFullName();
    }

    public function getRetailPrice(): ?int
    {
        return $this->retailPrice;
    }

    public function setRetailPrice(?int $retailPrice): self
    {
        $this->retailPrice = $retailPrice;

        return $this;
    }

    public function getRetailPriceUpdatedBy(): ?Admin
    {
        return $this->retailPriceUpdatedBy;
    }

    public function setRetailPriceUpdatedBy(?Admin $retailPriceUpdatedBy): self
    {
        $this->retailPriceUpdatedBy = $retailPriceUpdatedBy;

        return $this;
    }
}
