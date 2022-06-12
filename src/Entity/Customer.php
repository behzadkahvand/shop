<?php

namespace App\Entity;

use App\Dictionary\OrderStatus;
use App\Entity\Common\Timestampable;
use App\Repository\CustomerRepository;
use App\Service\Cart\Exceptions\CartNotFoundException;
use App\Validator as AppAssert;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="customers")
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 *
 * @UniqueEntity(
 *     fields={"mobile"},
 *     errorPath="mobile",
 *     message="This mobile number is already exists.",
 *     groups={"otp.send"}
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="This email is already used.",
 *     groups={"customer.update", "customer.customer.update"}
 * )
 * @UniqueEntity(
 *     fields={"nationalNumber"},
 *     errorPath="nationalNumber",
 *     message="This national number is already used.",
 *     groups={"customer.update", "customer.customer.update"}
 * )
 */
class Customer implements UserInterface, PasswordAuthenticatedUserInterface, ActivableUserInterface
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"default", "customer.read", "order.index", "order.show", "customer.list"})
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={"customer.update", "customer.customer.update", "order.store"})
     * @AppAssert\PersianEnglishChars(groups={"customer.update", "customer.customer.update", "order.store"})
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "customer.read",
     *     "order.show",
     *     "customer.customer.read",
     *     "cart.show",
     *     "customer.auth.profile",
     *     "orderShipment.show",
     *     "admin.seller.order.items.update_status",
     *     "customer.list",
     *     "carrier.inquiry.show",
     *     "customer.shipmentPrint",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={"customer.update", "customer.customer.update", "order.store"})
     * @AppAssert\PersianEnglishChars(groups={"customer.update", "customer.customer.update", "order.store"})
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "customer.read",
     *     "order.show",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     *     "orderShipment.show",
     *     "admin.seller.order.items.update_status",
     *     "customer.list",
     *     "carrier.inquiry.show",
     *     "customer.shipmentPrint",
     * })
     */
    private $family;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     *
     * @Assert\Email(groups={"customer.update", "customer.customer.update"})
     *
     * @Groups({"default", "customer.read", "customer.customer.read", "customer.auth.profile"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={"customer.update"})
     * @Assert\Type("string", groups={"customer.update"})
     *
     * @Groups({"default", "customer.read"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Choice(callback={"App\Dictionary\CustomerGenderDictionary", "toArray"}, groups={"customer.update",
     *     "customer.customer.update"})
     *
     * @Groups({"default", "customer.read", "customer.customer.read", "customer.auth.profile"})
     */
    private $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\NotBlank(groups={"customer.update"})
     * @Groups({"customer.auth.profile"})
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=15, unique=true)
     *
     * @Assert\NotBlank(groups={"customer.update"})
     * @AppAssert\Mobile(groups={"customer.update"})
     *
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     *     "orderShipment.show",
     *     "order.show",
     *     "carrier.inquiry.show",
     *     "orderAddress.shipmentPrint",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=10, nullable=true, unique=true)
     * @AppAssert\NationalNumber(groups={"customer.update", "customer.customer.update"})
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     *     "order.show",
     *     "orderShipment.show",
     * })
     */
    private $nationalNumber;

    /**
     * @ORM\OneToOne(targetEntity=Cart::class, mappedBy="customer")
     */
    private $cart;

    /**
     * @ORM\OneToMany(
     *     targetEntity=CustomerAddress::class,
     *     mappedBy="customer",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @Groups({"customer.read", "customer.update"})
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="customer")
     */
    private $orders;

    /**
     * @ORM\OneToOne(targetEntity=Account::class, mappedBy="customer", cascade={"persist", "remove"})
     *
     * @Assert\Valid(groups={"customer.customer.update"})
     *
     * @Groups({"customer.read", "customer.customer.read", "customer.auth.profile"})
     */
    private $account;

    /**
     * @Assert\NotBlank(groups={"admin.create"})
     * @SerializedName("password")
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity=Wishlist::class, mappedBy="customer", orphanRemoval=true)
     */
    private $wishlist;

    /**
     * @ORM\ManyToMany(targetEntity=PromotionCoupon::class, mappedBy="customers")
     */
    private $promotionCoupons;

    /**
     * @ORM\OneToMany(targetEntity=RateAndReview::class, mappedBy="customer", orphanRemoval=true)
     */
    private $rateAndReviews;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "customer.read",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     *     "order.show",
     *     "orderShipment.show",
     * })
     */
    private $isForeigner = false;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     *
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     *     "order.show",
     *     "orderShipment.show",
     * })
     */
    private $pervasiveCode;

    /**
     * @ORM\OneToOne(targetEntity=CustomerLegalAccount::class, mappedBy="customer", cascade={"persist", "remove"})
     * @Groups({
     *     "customer.read",
     * })
     */
    private $legalAccount;

    /**
     * @ORM\OneToMany(targetEntity=ProductNotifyRequest::class, mappedBy="customer", orphanRemoval=true)
     */
    private $productNotifyRequests;

    /**
     * @ORM\OneToOne(targetEntity=Wallet::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     * })
     */
    private $wallet;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     *
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "customer.customer.read",
     *     "customer.auth.profile",
     * })
     */
    private $isActive;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->wishes = new ArrayCollection();
        $this->wishlist = new ArrayCollection();
        $this->promotionCoupons = new ArrayCollection();
        $this->rateAndReviews = new ArrayCollection();
        $this->productNotifyRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     *
     * @return Customer
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->mobile;
    }

    public function getGender(): string
    {
        return (string) $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $date): self
    {
        $this->birthday = $date;

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

    public function getNationalNumber(): ?string
    {
        return $this->nationalNumber;
    }

    public function setNationalNumber(?string $nationalNumber): self
    {
        $this->nationalNumber = $nationalNumber;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(?string $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCartOrFail(): Cart
    {
        $cart = $this->getCart();

        if ($cart === null) {
            throw new CartNotFoundException();
        }

        return $cart;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): void
    {
        $this->cart = $cart;
    }

    /**
     * @return Collection|CustomerAddress[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(CustomerAddress $address): self
    {
        if (! $this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setCustomer($this);
        }

        return $this;
    }

    public function removeAddress(CustomerAddress $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getCustomer() === $this) {
                $address->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (! $this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setCustomer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;

        // set the owning side of the relation if necessary
        if ($account->getCustomer() !== $this) {
            $account->setCustomer($this);
        }

        return $this;
    }

    /**
     * @return Collection|Wishlist[]
     */
    public function getWishlist(): Collection
    {
        return $this->wishlist;
    }

    public function addWishlist(Wishlist $wishlist): self
    {
        if (! $this->wishlist->contains($wishlist)) {
            $this->wishlist[] = $wishlist;
            $wishlist->setCustomer($this);
        }

        return $this;
    }

    public function removeWishlist(Wishlist $wishlist): self
    {
        if ($this->wishlist->contains($wishlist)) {
            $this->wishlist->removeElement($wishlist);
            // set the owning side to null (unless already changed)
            if ($wishlist->getCustomer() === $this) {
                $wishlist->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"default", "customer.read", "customer.customer.read", "order.show", "orderShipment.show"})
     *
     * @SerializedName("birthday")
     */
    public function getFormattedBirthday(): ?string
    {
        $birthday = $this->getBirthday();

        return $birthday !== null ? $birthday->format('Y-m-d') : null;
    }

    /**
     * @Groups({"customer.auth.profile"})
     *
     * @SerializedName("isProfileCompleted")
     */
    public function isProfileCompleted(): bool
    {
        return $this->getName() !== null &&
            $this->getFamily() !== null &&
            $this->getNationalNumber() !== null;
    }

    /**
     * @return Collection|PromotionCoupon[]
     */
    public function getPromotionCoupons(): Collection
    {
        return $this->promotionCoupons;
    }

    public function addPromotionCoupon(PromotionCoupon $promotionCoupon): self
    {
        if (!$this->promotionCoupons->contains($promotionCoupon)) {
            $this->promotionCoupons[] = $promotionCoupon;
            $promotionCoupon->addCustomer($this);
        }

        return $this;
    }

    public function removePromotionCoupon(PromotionCoupon $promotionCoupon): self
    {
        if ($this->promotionCoupons->removeElement($promotionCoupon)) {
            $promotionCoupon->removeCustomer($this);
        }

        return $this;
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
            $rateAndReview->setCustomer($this);
        }

        return $this;
    }

    public function removeRateAndReview(RateAndReview $rateAndReview): self
    {
        if ($this->rateAndReviews->removeElement($rateAndReview)) {
            // set the owning side to null (unless already changed)
            if ($rateAndReview->getCustomer() === $this) {
                $rateAndReview->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @SerializedName("ordersHistory")
     *
     * @Groups({"order.index", "order.show", "orderShipment.show"})
     *
     * @OA\Property(
     *     type="object",
     *     @OA\Property(property="NEW", type="integer"),
     *     @OA\Property(property="WAIT_CUSTOMER", type="integer"),
     *     @OA\Property(property="CALL_FAILED", type="integer"),
     *     @OA\Property(property="WAITING_FOR_PAY", type="integer"),
     *     @OA\Property(property="CONFIRMED", type="integer"),
     *     @OA\Property(property="DELIVERED", type="integer"),
     *     @OA\Property(property="CANCELED", type="integer"),
     *     @OA\Property(property="CANCELED_SYSTEM", type="integer"),
     *     @OA\Property(property="REFUND", type="integer"),
     * )
     */
    public function getOrdersHistory(): array
    {
        $ordersSummary = collect($this->orders)->groupBy(fn (Order $order) => $order->getStatus())->map->count();

        return collect(OrderStatus::toArray())->map(fn () => 0)->merge($ordersSummary)->toArray();
    }

    public function getFullName()
    {
        return $this->getName() . ' ' . $this->getFamily();
    }

    public function getIsForeigner(): bool
    {
        return $this->isForeigner;
    }

    public function setIsForeigner(bool $isForeigner): self
    {
        $this->isForeigner = $isForeigner;

        return $this;
    }

    public function getPervasiveCode(): ?string
    {
        return $this->pervasiveCode;
    }

    public function setPervasiveCode(?string $pervasiveCode): self
    {
        $this->pervasiveCode = $pervasiveCode;

        return $this;
    }

    public function getLegalAccount(): ?CustomerLegalAccount
    {
        return $this->legalAccount;
    }

    public function setLegalAccount(CustomerLegalAccount $legalAccount): self
    {
        // set the owning side of the relation if necessary
        if ($legalAccount->getCustomer() !== $this) {
            $legalAccount->setCustomer($this);
        }

        $this->legalAccount = $legalAccount;

        return $this;
    }

    /**
     * @SerializedName("isProfileLegal")
     * @Groups({
     *     "default",
     *     "customer.auth.profile",
     *     "customer.read",
     * })
     */
    public function isProfileLegal(): bool
    {
        return null !== $this->legalAccount;
    }

    /**
     * @return Collection|ProductNotifyRequest[]
     */
    public function getProductNotifyRequests(): Collection
    {
        return $this->productNotifyRequests;
    }

    public function addProductNotifyRequest(ProductNotifyRequest $productNotifyRequest): self
    {
        if (!$this->productNotifyRequests->contains($productNotifyRequest)) {
            $this->productNotifyRequests[] = $productNotifyRequest;
            $productNotifyRequest->setCustomer($this);
        }

        return $this;
    }

    public function removeProductNotifyRequest(ProductNotifyRequest $productNotifyRequest): self
    {
        if ($this->productNotifyRequests->removeElement($productNotifyRequest)) {
            // set the owning side to null (unless already changed)
            if ($productNotifyRequest->getCustomer() === $this) {
                $productNotifyRequest->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->mobile;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getWalletBalance(): int
    {
        return $this->getWallet()->getBalance();
    }

    public function hasWallet(): bool
    {
        return null !== $this->wallet;
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
