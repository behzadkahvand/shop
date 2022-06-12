<?php

namespace App\Entity;

use App\Dictionary\SellerOrderItemStatus;
use App\Repository\SellerOrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="seller_order_items")
 * @ORM\Entity(repositoryClass=SellerOrderItemRepository::class)
 */
class SellerOrderItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "seller.order.items.index",
     *     "admin.seller.order_items.index",
     *     "admin.seller.order.items.index",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "order.show",
     *     "orderShipment.show",
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "order.items",
     *     "return_request.show"
     * })
     */
    private $status = SellerOrderItemStatus::WAITING;

    /**
     * @ORM\OneToOne(targetEntity=OrderItem::class, inversedBy="sellerOrderItem")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order_items.index",
     * })
     */
    private $orderItem;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class, inversedBy="orderItems", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"seller.package.show"})
     */
    private $seller;

    /**
     * @ORM\ManyToOne(targetEntity=SellerPackageItem::class, inversedBy="orderItems")
     */
    private $packageItem;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"admin.seller.order_items.index"})
     */
    private $sendDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "admin.seller.order.items.update_status",
     *     "order.show",
     *     "orderShipment.show",
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "order.items",
     * })
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=SellerOrderItemStatusLog::class, mappedBy="sellerOrderItem")
     */
    private $sellerOrderItemStatusLogs;

    public function __construct()
    {
        $this->sellerOrderItemStatusLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(OrderItem $orderItem): self
    {
        $this->orderItem = $orderItem;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPackageItem(): ?SellerPackageItem
    {
        return $this->packageItem;
    }

    public function setPackageItem(?SellerPackageItem $packageItem): self
    {
        $this->packageItem = $packageItem;

        return $this;
    }

    public function getSellerOrderItemStatusLog(): ?Collection
    {
        return $this->sellerOrderItemStatusLogs;
    }

    public function addSellerOrderItemStatusLog(SellerOrderItemStatusLog $sellerOrderItemStatusLog): self
    {
        if (!$this->sellerOrderItemStatusLogs->contains($sellerOrderItemStatusLog)) {
            $this->sellerOrderItemStatusLogs[] = $sellerOrderItemStatusLog;
            $sellerOrderItemStatusLog->setSellerOrderItem($this);
        }

        return $this;
    }

    public function isSent(): bool
    {
        return SellerOrderItemStatus::SENT_BY_SELLER === $this->status;
    }

    public function isRejected(): bool
    {
        $statuses = [SellerOrderItemStatus::CANCELED_BY_USER, SellerOrderItemStatus::CANCELED_BY_SELLER];

        return in_array($this->status, $statuses, true);
    }

    public function isReceived(): bool
    {
        return SellerOrderItemStatus::RECEIVED === $this->status;
    }

    public function isStoraged(): bool
    {
        return SellerOrderItemStatus::STORAGED === $this->status;
    }

    public function isWaitingForSend(): bool
    {
        return SellerOrderItemStatus::WAITING_FOR_SEND === $this->status;
    }

    public function getSendDate(): ?\DateTimeInterface
    {
        return $this->sendDate;
    }

    public function setSendDate(\DateTimeInterface $sendDate): self
    {
        $this->sendDate = $sendDate;

        return $this;
    }

    /**
     * @Groups({"seller.order.items.index"})
     * @SerializedName("deliveryDate")
     */
    public function getFormattedDeliveryDate(): string
    {
        if ($this->sendDate === null) {
            $orderShipment               = $this->getOrderItem()->getOrderShipment();
            $sellerFormattedDeliveryDate = $orderShipment->getSellerFormattedDeliveryDate();
            $sendDate                    = new \DateTimeImmutable($sellerFormattedDeliveryDate);
        } else {
            $sendDate = new \DateTimeImmutable($this->sendDate->format('Y-m-d'));
        }

        return $sendDate->setTime(12, 0, 0)->format('Y-m-d H:i:s');
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

    /**
     * @Groups({
     *     "admin.seller.order.items.index",
     * })
     */
    public function getSingleItemShipment(): bool
    {
        return $this->getOrderItem()->getOrderShipment()->getOrderItemsCount() === 1;
    }

    public function isDelivered(): bool
    {
        return SellerOrderItemStatus::DELIVERED === $this->status;
    }
}
