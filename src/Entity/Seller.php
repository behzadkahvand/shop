<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\SellerRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="sellers")
 * @ORM\Entity(repositoryClass=SellerRepository::class)
 *
 * @UniqueEntity(fields={"username"}, groups={"create", "update"})
 *
 * @UniqueEntity(
 *     fields={"mobile"},
 *     errorPath="mobile",
 *     message="This mobile number is already exists.",
 *     groups={"seller.create", "seller.update"}
 * )
 */
class Seller implements UserInterface, PasswordAuthenticatedUserInterface, ActivableUserInterface
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "default",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "seller.index",
     *     "seller.show",
     *     "seller.update",
     *     "seller.create",
     *     "order.show",
     *     "customer.product.show",
     *     "product.index",
     *     "product.show",
     *     "variant.show",
     *     "admin.seller.order_items.index",
     *     "order.items",
     *     "customer.rateAndReview.index",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "campaignCommission.show",
     *     "seller.best_sellers",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true, unique=true)
     *
     * @Groups({
     *     "customer.product.show",
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "seller.index",
     *     "seller.show",
     *     "default",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     *     "seller.package.show",
     *     "order.items",
     *     "admin.seller.order.items.index",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "orderShipment.show",
     *     "seller.order.items.index",
     *     "seller.best_sellers",
     *     "customer.product.rateAndReview.index"
     * })
     */
    private $identifier;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"create", "update"})
     *
     * @Groups({
     *     "inventories.index",
     *     "inventories.show",
     *     "inventories.store",
     *     "inventories.update",
     *     "order.show",
     *     "seller.index",
     *     "seller.show",
     *     "order.show",
     *     "default",
     *     "customer.product.show",
     *     "product.index",
     *     "product.show",
     *     "variant.show",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "orderShipment.show",
     *     "product.search",
     *     "seller.auth.profile",
     *     "admin.seller.order_items.index",
     *     "product.search.seller.filter",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     *     "seller.package.show",
     *     "order.items",
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "campaignCommission.show",
     *     "customer.layout.onSaleBlocks",
     *     "seller.best_sellers",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"create", "update"})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=15, unique=true, nullable=true)
     *
     * @Assert\NotBlank(groups={"seller.create", "seller.update"})
     * @AppAssert\Mobile(groups={"seller.create", "seller.update"})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "seller.best_sellers",
     * })
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @AppAssert\Phone(groups={"seller.create", "seller.update"})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     *     "admin.seller.order_items.index",
     * })
     */
    private $phone;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @AppAssert\Address(groups={"seller.create", "seller.update"})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     *     "seller.best_sellers",
     * })
     */
    private $address;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=10, nullable=true, unique=true)
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $nationalNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $nationalIdentifier;

    /**
     * @Assert\NotBlank(groups={"create"})
     * @SerializedName("password")
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $isLimited = false;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order_items.index",
     *     "seller.package.show",
     * })
     */
    private $pickup = false;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     *
     * * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     *     "order.show",
     *     "orderShipment.show"
     * })
     */
    private $isRetail = false;

    /**
     * @ORM\OneToMany(targetEntity=Warehouse::class, mappedBy="seller", orphanRemoval=true)
     */
    private $warehouses;

    /**
     * @ORM\OneToMany(targetEntity=Holiday::class, mappedBy="seller", orphanRemoval=true)
     */
    private $holidays;

    /**
     * @ORM\OneToMany(targetEntity=Inventory::class, mappedBy="seller", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $inventories;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="seller", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity=SellerOrderItem::class, mappedBy="seller", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $orderItems;

    /**
     * @ORM\OneToMany(targetEntity=SellerPackage::class, mappedBy="seller", orphanRemoval=true)
     */
    private $packages;

    /**
     * @ORM\OneToMany(targetEntity=InventoryUpdateDemand::class, mappedBy="seller", orphanRemoval=true)
     */
    private $inventoryUpdateDemands;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     *     "seller.best_sellers",
     * })
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $shebaNumber;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $paymentPeriod;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $checkoutPeriod;

    /**
     * @ORM\OneToOne(targetEntity=SellerScore::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, unique=true)
     *
     * @Groups({
     *     "customer.product.show",
     *     "seller.auth.profile",
     *     "seller.best_sellers",
     * })
     */
    private $score;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     *
     * @Groups({
     *     "seller.index",
     *     "seller.show",
     *     "seller.auth.profile",
     * })
     */
    private $isActive;

    public function __construct()
    {
        $this->warehouses = new ArrayCollection();
        $this->holidays = new ArrayCollection();
        $this->inventories = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->packages = new ArrayCollection();
        $this->inventoryUpdateDemands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

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
     * @return Collection|Warehouse[]
     */
    public function getWarehouses(): Collection
    {
        return $this->warehouses;
    }

    public function addWarehouse(Warehouse $warehouse): self
    {
        if (!$this->warehouses->contains($warehouse)) {
            $this->warehouses[] = $warehouse;
            $warehouse->setSeller($this);
        }

        return $this;
    }

    public function removeWarehouse(Warehouse $warehouse): self
    {
        if ($this->warehouses->contains($warehouse)) {
            $this->warehouses->removeElement($warehouse);
            // set the owning side to null (unless already changed)
            if ($warehouse->getSeller() === $this) {
                $warehouse->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Holiday[]
     */
    public function getHolidays(): Collection
    {
        return $this->holidays;
    }

    public function addHoliday(Holiday $holiday): self
    {
        if (!$this->holidays->contains($holiday)) {
            $this->holidays[] = $holiday;
            $holiday->setSeller($this);
        }

        return $this;
    }

    public function removeHoliday(Holiday $holiday): self
    {
        if ($this->holidays->contains($holiday)) {
            $this->holidays->removeElement($holiday);
            // set the owning side to null (unless already changed)
            if ($holiday->getSeller() === $this) {
                $holiday->setSeller(null);
            }
        }

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_SELLER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param  mixed  $plainPassword
     * @return Admin
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

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
        if (!$this->inventories->contains($inventory)) {
            $this->inventories[] = $inventory;
            $inventory->setSeller($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): self
    {
        if ($this->inventories->contains($inventory)) {
            $this->inventories->removeElement($inventory);
            // set the owning side to null (unless already changed)
            if ($inventory->getSeller() === $this) {
                $inventory->setSeller(null);
            }
        }

        return $this;
    }

    public function getNationalNumber(): ?string
    {
        return $this->nationalNumber;
    }

    public function setNationalNumber(string $nationalNumber): self
    {
        $this->nationalNumber = $nationalNumber;

        return $this;
    }

    public function getNationalIdentifier(): ?string
    {
        return $this->nationalIdentifier;
    }

    public function setNationalIdentifier(?string $nationalIdentifier): self
    {
        $this->nationalIdentifier = $nationalIdentifier;

        return $this;
    }

    public function getIsLimited(): bool
    {
        return $this->isLimited;
    }

    public function setIsLimited(bool $isLimited): self
    {
        $this->isLimited = $isLimited;

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
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setSeller($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getSeller() === $this) {
                $product->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SellerOrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(SellerOrderItem $sellerOrderItem): self
    {
        if (!$this->orderItems->contains($sellerOrderItem)) {
            $this->orderItems[] = $sellerOrderItem;
            $sellerOrderItem->setSeller($this);
        }

        return $this;
    }

    public function removeOrderItem(SellerOrderItem $sellerOrderItem): self
    {
        if ($this->orderItems->contains($sellerOrderItem)) {
            $this->orderItems->removeElement($sellerOrderItem);
            // set the owning side to null (unless already changed)
            if ($sellerOrderItem->getSeller() === $this) {
                $sellerOrderItem->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SellerPackage[]
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(SellerPackage $package): self
    {
        if (!$this->packages->contains($package)) {
            $this->packages[] = $package;
            $package->setSeller($this);
        }

        return $this;
    }

    public function removePackage(SellerPackage $package): self
    {
        if ($this->packages->contains($package)) {
            $this->packages->removeElement($package);
            // set the owning side to null (unless already changed)
            if ($package->getSeller() === $this) {
                $package->setSeller(null);
            }
        }

        return $this;
    }

    public function getPickup(): ?bool
    {
        return $this->pickup;
    }

    public function setPickup(bool $pickup): self
    {
        $this->pickup = $pickup;

        return $this;
    }

    public function getIsRetail(): ?bool
    {
        return $this->isRetail;
    }

    public function setIsRetail(bool $isRetail): self
    {
        $this->isRetail = $isRetail;

        return $this;
    }

    /**
     * @return Collection|InventoryUpdateDemand[]
     */
    public function getInventoryUpdateDemands(): Collection
    {
        return $this->inventoryUpdateDemands;
    }

    public function addInventoryUpdateDemand(InventoryUpdateDemand $inventoryUpdateDemand): self
    {
        if (!$this->inventoryUpdateDemands->contains($inventoryUpdateDemand)) {
            $this->inventoryUpdateDemands[] = $inventoryUpdateDemand;
            $inventoryUpdateDemand->setSeller($this);
        }

        return $this;
    }

    public function removeInventoryUpdateDemand(InventoryUpdateDemand $inventoryUpdateDemand): self
    {
        if ($this->inventoryUpdateDemands->removeElement($inventoryUpdateDemand)) {
            // set the owning side to null (unless already changed)
            if ($inventoryUpdateDemand->getSeller() === $this) {
                $inventoryUpdateDemand->setSeller(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getShebaNumber(): ?string
    {
        return $this->shebaNumber;
    }

    public function setShebaNumber(?string $shebaNumber): self
    {
        $this->shebaNumber = $shebaNumber;

        return $this;
    }

    public function getPaymentPeriod(): ?int
    {
        return $this->paymentPeriod;
    }

    public function setPaymentPeriod(?int $paymentPeriod): self
    {
        $this->paymentPeriod = $paymentPeriod;

        return $this;
    }

    public function getCheckoutPeriod(): ?int
    {
        return $this->checkoutPeriod;
    }

    public function setCheckoutPeriod(?int $checkoutPeriod): self
    {
        $this->checkoutPeriod = $checkoutPeriod;

        return $this;
    }

    public function setScore(SellerScore $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getScore(): ?SellerScore
    {
        return $this->score;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive($isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }


    public function isActive(): bool
    {
        return $this->getIsActive();
    }
}
