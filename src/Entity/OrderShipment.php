<?php

namespace App\Entity;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\TransactionStatus;
use App\Exceptions\OrderShipment\InvalidOrderShipmentStatusException;
use App\Repository\OrderShipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="order_shipments")
 * @ORM\Entity(repositoryClass=OrderShipmentRepository::class)
 */
class OrderShipment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.index",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "carrier.inquiry.show",
     *     "orderShipment.shipmentPrint",
     *     "orderShipment.show.driver",
     *     "admin.seller.order.items.index",
     * })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @Groups({"orderShipment.show","orderShipment.index","orderShipment.shipmentPrint"})
     */
    private $subTotal;

    /**
     * @var int
     *
     *
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"orderShipment.show", "customer.order.show","orderShipment.index","orderShipment.shipmentPrint"})
     */
    private $grandTotal;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.index",
     *     "customer.order.show",
     *     "admin.seller.order_items.index",
     *     "orderShipment.shipmentPrint",
     * })
     */
    private $status = OrderShipmentStatus::NEW;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $signature;

    /**
     * @ORM\ManyToOne(targetEntity=ShippingMethod::class, inversedBy="shipments")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "customer.order.show",
     *     "orderShipment.show",
     *     "orderShipment.shipmentPrint",
     *     "orderShipment.index",
     *     "order.index",
     * })
     */
    private $method;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="shipments")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "orderShipment.index",
     *     "orderShipment.show",
     *     "carrier.inquiry.show",
     * })
     */
    private $order;

    /**
     * @ORM\OneToOne(targetEntity=Transaction::class)
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.show.driver"
     * })
     */
    private $transaction;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="orderShipment")
     * @MaxDepth(1)
     *
     * @Groups({
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "carrier.inquiry.show",
     *     "orderShipment.shipmentPrint"
     * })
     */
    private $orderItems;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"orderShipment.show", "orderShipment.shipmentPrint","customer.order.show"})
     */
    private $trackingCode;

    /**
     * @ORM\ManyToOne(targetEntity=ShippingPeriod::class, inversedBy="shipments")
     *
     * @Groups({"orderShipment.show", "customer.order.show", "orderShipment.index", "orderShipment.shipmentPrint"})
     */
    private $period;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"seller.order.items.index"})
     */
    private $title;

    /**
     * @var array<int>
     *
     * @ORM\Column(type="json", nullable=true)
     *
     * @Groups({"orderShipment.show", "orderShipment.shipmentPrint"})
     */
    private $categoryDeliveryRange;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date")
     *
     * @Groups({"customer.order.show", "orderShipment.show", "orderShipment.shipmentPrint"})
     */
    private $deliveryDate;

    /**
     * @var ShippingCategory
     *
     * @ORM\ManyToOne(targetEntity=ShippingCategory::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "order.show",
     * })
     */
    private $shippingCategory;

    /**
     * @ORM\OneToMany(targetEntity=OrderShipmentStatusLog::class, mappedBy="orderShipment")
     * @Groups({"orderShipment.show"})
     */
    private $orderShipmentStatusLogs;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"orderShipment.show",})
     */
    protected $createdBy;

    /**
     * @ORM\OneToMany(targetEntity=OrderPromotionDiscount::class, mappedBy="orderShipment")
     * @Groups({"orderShipment.show", "orderShipment.embedded.withDiscount"})
     */
    private $discounts;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"customer.order.show", "orderShipment.shipmentPrint"})
     */
    private $description;

    /**
     * Proof of Delivery code -> podCode
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     *
     * @Groups({"orderShipment.show.driver",})
     */
    private $podCode;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.index",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "carrier.inquiry.show",
     * })
     */
    private $isPrinted = false;

    /**
     * @ORM\Column(type="smallint", options={"unsigned"=true, "default"=0})
     */
    private $packagedCount = 0;

    public function __construct()
    {
        $this->orderItems              = new ArrayCollection();
        $this->orderShipmentStatusLogs = new ArrayCollection();
        $this->discounts               = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSubTotal(): int
    {
        return $this->subTotal;
    }

    /**
     * @param int $subTotal
     *
     * @return OrderShipment
     */
    public function setSubTotal(int $subTotal): OrderShipment
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getGrandTotal(): int
    {
        return $this->grandTotal;
    }

    /**
     * @Groups({"orderShipment.index"})
     * @SerializedName("sumGrandTotal")
     */
    public function getSumGrandTotal(): int
    {
        $orderItems = $this->getOrderItems();
        $sum        = 0;
        foreach ($orderItems as $orderItem) {
            $sum += $orderItem->getGrandTotal();
        }

        return $sum;
    }

    /**
     * @param int $grandTotal
     *
     * @return OrderShipment
     */
    public function setGrandTotal(int $grandTotal): OrderShipment
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, OrderShipmentStatus::toArray())) {
            throw new InvalidOrderShipmentStatusException();
        }

        $this->status = $status;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getMethod(): ?ShippingMethod
    {
        return $this->method;
    }

    public function setMethod(?ShippingMethod $method): self
    {
        $this->method = $method;

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

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    /**
     * @Groups({"orderShipment.index"})
     * @SerializedName("orderItemsCount")
     */
    public function getOrderItemsCount(): int
    {
        return $this->orderItems->count();
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setOrderShipment($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
        }

        return $this;
    }

    public function getTrackingCode(): ?string
    {
        return $this->trackingCode;
    }

    public function setTrackingCode(?string $trackingCode): self
    {
        $this->trackingCode = $trackingCode;

        return $this;
    }

    public function getPeriod(): ?ShippingPeriod
    {
        return $this->period;
    }

    public function setPeriod(?ShippingPeriod $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|OrderShipmentStatusLog[]
     */
    public function getOrderShipmentStatusLogs(): Collection
    {
        return $this->orderShipmentStatusLogs;
    }

    public function addOrderShipmentStatusLog(OrderShipmentStatusLog $orderShipmentStatusLog): self
    {
        if (!$this->orderShipmentStatusLogs->contains($orderShipmentStatusLog)) {
            $this->orderShipmentStatusLogs[] = $orderShipmentStatusLog;
            $orderShipmentStatusLog->setOrderShipment($this);
        }

        return $this;
    }

    public function removeOrderShipmentStatusLog(OrderShipmentStatusLog $orderShipmentStatusLog): self
    {
        if ($this->orderShipmentStatusLogs->contains($orderShipmentStatusLog)) {
            $this->orderShipmentStatusLogs->removeElement($orderShipmentStatusLog);
            // set the owning side to null (unless already changed)
            if ($orderShipmentStatusLog->getOrderShipment() === $this) {
                $orderShipmentStatusLog->setOrderShipment(null);
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getCategoryDeliveryRange(): ?array
    {
        return $this->categoryDeliveryRange;
    }

    /**
     * @param array $categoryDeliveryRange
     *
     * @return OrderShipment
     */
    public function setCategoryDeliveryRange(?array $categoryDeliveryRange)
    {
        $this->categoryDeliveryRange = $categoryDeliveryRange;

        return $this;
    }

    /**
     * @Groups({"orderShipment.show", "orderShipment.index"})
     * @SerializedName("deliveryDate")
     *
     * @return string
     */
    public function getFormattedDeliveryDate(): string
    {
        return $this->deliveryDate->format('Y-m-d H:i');
    }

    /**
     * @return string
     */
    public function getSellerFormattedDeliveryDate(): string
    {
        $deliveryData = clone $this->deliveryDate;

        if ($this->period !== null && $this->period->getStart()->format('H:i:s') === '09:00:00') {
            return $deliveryData->modify('-1 day')
                                ->setTime(12, 0, 0)
                                ->format('Y-m-d H:i:s');
        }

        return $deliveryData->setTime(12, 0, 0)->format('Y-m-d H:i:s');
    }

    /**
     * @return ShippingCategory
     */
    public function getShippingCategory(): ShippingCategory
    {
        return $this->shippingCategory;
    }

    /**
     * @param ShippingCategory $shippingCategory
     *
     * @return OrderShipment
     */
    public function setShippingCategory(ShippingCategory $shippingCategory): self
    {
        $this->shippingCategory = $shippingCategory;

        return $this;
    }

    public function getTotalOrderItemPrices(): int
    {
        return collect($this->orderItems)->sum(fn(OrderItem $orderItem) => $orderItem->getGrandTotal());
    }

    /**
     * @SerializedName("isPaid")
     * @Groups({"orderShipment.show","orderShipment.index", "customer.order.show"})
     */
    public function isPaid(): bool
    {
        if ($this->order) {
            if ($this->order->isPaid()) {
                return true;
            }

            if (!$this->order->hasOfflinePaymentMethod()) {
                return false;
            }

            return $this->transaction && $this->transaction->getStatus() === TransactionStatus::SUCCESS;
        }

        return false;
    }

    public function isCanceled(): bool
    {
        return $this->status === OrderShipmentStatus::CANCELED;
    }

    public function isCanceledByCustomer(): bool
    {
        return $this->status === OrderShipmentStatus::CANCELED_BY_CUSTOMER;
    }

    public function isDelivered(): bool
    {
        return $this->status === OrderShipmentStatus::DELIVERED;
    }

    public function isWaitingForSend(): bool
    {
        return OrderShipmentStatus::WAITING_FOR_SEND === $this->status;
    }

    public function isPreparing(): bool
    {
        return OrderShipmentStatus::PREPARING === $this->status;
    }

    public function isPrepared(): bool
    {
        return OrderShipmentStatus::PREPARED === $this->status;
    }

    public function isPackaged(): bool
    {
        return OrderShipmentStatus::PACKAGED === $this->status;
    }

    public function getOrderItemsInfo(): array
    {
        $shipmentInfo = [
            'items_grand_total' => 0,
            'inventory_ids'     => [],
        ];

        foreach ($this->getOrderItems() as $orderItem) {
            $shipmentInfo['items_grand_total']                                  += $orderItem->getGrandTotal();
            $shipmentInfo['inventory_ids'][$orderItem->getInventory()->getId()] = $orderItem->getGrandTotal();
        }

        return $shipmentInfo;
    }

    /**
     * @return Collection|OrderPromotionDiscount[]
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function getDiscountsCount(): int
    {
        return $this->discounts->count();
    }

    public function addDiscount(OrderPromotionDiscount $discount): self
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts[] = $discount;
            $discount->setOrderShipment($this);
        }

        return $this;
    }

    public function removeDiscount(OrderPromotionDiscount $discount): self
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getOrderShipment() === $this) {
                $discount->setOrderShipment(null);
            }
        }

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

    public function getPodCode(): ?int
    {
        return $this->podCode;
    }

    public function setPodCode(?int $podCode): self
    {
        $this->podCode = $podCode;

        return $this;
    }

    /**
     * @SerializedName("discount")
     *
     * @Groups({
     *     "carrier.inquiry.show",
     * })
     */
    public function getTotalDiscount(): int
    {
        $totalDiscount = 0;
        $this->getDiscounts()->forAll(function ($index, OrderPromotionDiscount $discount) use (&$totalDiscount): bool {
            $totalDiscount += $discount->getAmount();

            return true;
        });

        return $totalDiscount;
    }

    /**
     * @SerializedName("payable")
     *
     * @Groups({
     *     "carrier.inquiry.show",
     *     "orderShipment.show.driver",
     * })
     */
    public function getPayable(): int
    {
        $payable = $this->getTotalOrderItemPrices() + $this->getGrandTotal() - $this->getTotalDiscount();

        return $payable * 10; // Convert Toman to Rials
    }

    public function getIsPrinted(): ?bool
    {
        return $this->isPrinted;
    }

    public function setIsPrinted(bool $isPrinted): self
    {
        $this->isPrinted = $isPrinted;

        return $this;
    }

    public function markAsPrinted()
    {
        $this->setIsPrinted(true);
    }


    /**
     * @return \DateTimeInterface
     *
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getPromisedDeliveryDate(): \DateTimeInterface
    {
        if (empty($range = $this->getCategoryDeliveryRange()) || !isset($range[1])) {
            return $this->getDeliveryDate();
        }

        /** @var \DateTime $promisedDeliveryDate */
        $promisedDeliveryDate = clone $this->getDeliveryDate();
        $promisedDeliveryDate->add(new \DateInterval("P{$range[1]}D"));

        return $promisedDeliveryDate;
    }

    /**
     * @return string|null
     *
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getOrderIdentifier()
    {
        if (null === $this->getOrder()) {
            return null;
        }

        return $this->getOrder()->getIdentifier();
    }

    /**
     * @return string|null
     *
     * @Groups({"orderShipment.shipmentPrint", "orderShipment.show.driver",})
     */
    public function getOrderPaymentMethod()
    {
        if (null === $this->getOrder()) {
            return null;
        }

        return $this->getOrder()->getPaymentMethod();
    }

    /**
     * @return int
     *
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getDiscountAmount(): int
    {
        $discountAmount = 0;

        foreach ($this->getOrderItems() as $orderItem) {
            $discountAmount += $orderItem->getDiscountAmount();
        }

        return $discountAmount;
    }

    /**
     * @return ?OrderAddress
     *
     * @Groups({"orderShipment.shipmentPrint", "orderShipment.show.driver",})
     */
    public function getOrderAddress()
    {
        if (null === $this->getOrder()) {
            return null;
        }

        return $this->getOrder()->getOrderAddress();
    }

    /**
     * @return ?Customer
     *
     * @Groups({"orderShipment.shipmentPrint"})
     */
    public function getCustomer()
    {
        if (null === $this->getOrder()) {
            return null;
        }

        return $this->getOrder()->getCustomer();
    }

    public function isExpress(): bool
    {
        return null !== $this->period;
    }

    public function getPackagedCount(): ?int
    {
        return $this->packagedCount;
    }

    public function setPackagedCount(int $packagedCount): self
    {
        $this->packagedCount = $packagedCount;

        return $this;
    }

    public function increasePackagedCount(int $step = 1): self
    {
        $this->packagedCount += (int) abs($step);

        return $this;
    }

    public function isShipmentFullyStoraged(): bool
    {
        $orderItems = collect($this->getOrderItems())->filter(
            fn(OrderItem $oi) => !$oi->getSellerOrderItem()->isRejected()
        );

        return $orderItems->isNotEmpty() && $orderItems->every(
            fn(OrderItem $oi) => $oi->getSellerOrderItem()->isStoraged()
        );
    }

    public function hasOnlyOneDistinctItem(): bool
    {
        return $this->getOrderItemsCount() === 1;
    }

    public function hasItems(): bool
    {
        return $this->getOrderItemsCount() > 0;
    }

    public function setCreatedBy(?Admin $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?Admin
    {
        return $this->createdBy;
    }
}
