<?php

namespace App\Entity;

use App\Dictionary\SellerPackageStatus;
use App\Entity\Common\Timestampable;
use App\Repository\SellerPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nelmio\ApiDocBundle\Annotation\Model;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="seller_packages")
 * @ORM\Entity(repositoryClass=SellerPackageRepository::class)
 */
class SellerPackage
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.index",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order.items.index",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.index",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order.items.index",
     * })
     */
    private $status = SellerPackageStatus::SENT;

    /**
     * @ORM\Column(type="string", length=32)
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.index",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order.items.index",
     * })
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.index",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order.items.index",
     * })
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     *     "seller.package.show",
     * })
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({
     *     "admin.seller.order.items.index",
     * })
     */
    private $autoCreation = false;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class, inversedBy="packages")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "seller.package.show",
     * })
     */
    private $seller;

    /**
     * @ORM\OneToMany(targetEntity=SellerPackageItem::class, mappedBy="package", orphanRemoval=true)
     * @Groups({
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "admin.seller.order.items.update_status",
     * })
     */
    private $items;

    /**
     * @ORM\OneToMany(targetEntity=SellerPackageStatusLog::class, mappedBy="sellerPackage", orphanRemoval=true)
     */
    private $statusLogs;

    public function __construct()
    {
        $this->sentAt = new \DateTime();
        $this->items = new ArrayCollection();
        $this->statusLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
     * @return Collection|SellerPackageItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(SellerPackageItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setPackage($this);
        }

        return $this;
    }

    public function removeItem(SellerPackageItem $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getPackage() === $this) {
                $item->setPackage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SellerPackageStatusLog[]
     */
    public function getStatusLogs(): Collection
    {
        return $this->statusLogs;
    }

    public function addStatusLog(SellerPackageStatusLog $statusLog): self
    {
        if (!$this->statusLogs->contains($statusLog)) {
            $this->statusLogs[] = $statusLog;
            $statusLog->setSellerPackage($this);
        }

        return $this;
    }

    public function removeStatusLog(SellerPackageStatusLog $statusLog): self
    {
        if ($this->statusLogs->contains($statusLog)) {
            $this->statusLogs->removeElement($statusLog);
            // set the owning side to null (unless already changed)
            if ($statusLog->getSellerPackage() === $this) {
                $statusLog->setSellerPackage(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"admin.seller.order.items.index"})
     *
     * @SerializedName("packageItems")
     *
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref=@Model(type=SellerOrderItem::class))
     * )
     */
    public function getPackageOrderItems(): Collection
    {
        $orderItems = collect($this->items)->flatMap(function (SellerPackageItem $packageItem) {
            return $packageItem->getOrderItems()->toArray();
        });

        return new ArrayCollection($orderItems->toArray());
    }

    /**
     * @Groups({"seller.package.index", "seller.package.show"})
     *
     * @return int
     */
    public function getItemsTotalQuantity(): int
    {
        return collect($this->items)->sum(fn(SellerPackageItem $pi) => $pi->getQuantity() ?? 0);
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isAutoCreation(): bool
    {
        return $this->autoCreation;
    }

    public function setAutoCreation(bool $autoCreation): self
    {
        $this->autoCreation = $autoCreation;
        return $this;
    }
}
