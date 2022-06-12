<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\SellerPackageItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="seller_package_items")
 * @ORM\Entity(repositoryClass=SellerPackageItemRepository::class)
 */
class SellerPackageItem
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=SellerPackage::class, inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $package;

    /**
     * @ORM\OneToMany(targetEntity=SellerOrderItem::class, mappedBy="packageItem")
     * @Groups({
     *     "seller.order.items.sent",
     *     "admin.seller.order.items.update_status",
     *     "seller.package.show",
     * })
     */
    private $orderItems;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("seller.package.show")
     */
    private $description;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public static function fromSellerOrderItem(SellerOrderItem $sellerOrderItem, SellerPackage $package = null): self
    {
        $item = new static();
        $item->addOrderItem($sellerOrderItem);

        if ($package) {
            $package->addItem($item);
        }

        return $item;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPackage(): ?SellerPackage
    {
        return $this->package;
    }

    public function setPackage(?SellerPackage $package): self
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return Collection|SellerOrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(SellerOrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setPackageItem($this);
        }

        return $this;
    }

    public function removeOrderItem(SellerOrderItem $orderItem): self
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
            // set the owning side to null (unless already changed)
            if ($orderItem->getPackageItem() === $this) {
                $orderItem->setPackageItem(null);
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

    /**
     * @Groups({
     *     "seller.order.items.sent",
     *     "admin.seller.order.items.update_status",
     *     "seller.package.show",
     * })
     */
    public function getQuantity(): int
    {
        return array_sum($this->getOrderItems()->map(function (SellerOrderItem $soi) {
            $orderItem = $soi->getOrderItem();

            return $orderItem ? $orderItem->getQuantity() : 0;
        })->toArray());
    }
}
