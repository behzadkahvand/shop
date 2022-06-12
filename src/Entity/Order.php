<?php

namespace App\Entity;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Dictionary\TransactionStatus;
use App\Entity\Common\Timestampable;
use App\Exceptions\Order\InvalidOrderStatusException;
use App\Repository\OrderRepository;
use App\Service\Carrier\Exceptions\InvalidPaymentMethodException;
use App\Service\Order\UpdateOrderItems\Exceptions\OrderDocumentNotFoundException;
use App\Service\Promotion\LockablePromotionSubjectInterface;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use ReflectionException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 */
class Order implements LockablePromotionSubjectInterface
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "order.show",
     *     "customer.order.index",
     *     "customer.order.show",
     *     "seller.order.items.index",
     *     "seller.order.items.sent",
     *     "seller.package.show",
     *     "orderShipment.show",
     *     "order.notes",
     *     "order.notes.index",
     *     "return_request.show",
     *     "return_request.index",
     *     "wallet_history.show",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "order.show",
     *     "customer.order.index",
     *     "customer.order.show",
     *     "seller.order.items.index",
     *     "admin.seller.order_items.index"
     * })
     */
    private $status = OrderStatus::NEW;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "order.show",
     *     "customer.order.index",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "admin.seller.order_items.index",
     *     "order.legal.account.store",
     *     "wallet_history.show",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $identifier;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"default", "order.index", "order.show", "customer.order.index", "customer.order.show"})
     */
    private $subtotal;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"default", "order.index", "order.show", "customer.order.index", "customer.order.show"})
     */
    private $grandTotal;

    /**
     * @ORM\Column(type="bigint", options={"default"=0})
     *
     * @Groups({"default", "order.index", "order.show",})
     */
    private $balanceAmount;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"default", "order.index", "order.show","orderShipment.index", "orderShipment.show"})
     */
    private $paymentMethod;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({
     *     "order.index",
     *     "order.show",
     *     "orderShipment.show",
     *     "admin.seller.order.items.update_status",
     *     "carrier.inquiry.show",
     * })
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity=OrderShipment::class, mappedBy="order", cascade={"persist", "remove"})
     *
     * @Groups({"customer.order.show", "order.index", "order.show"})
     */
    private $shipments;

    /**
     * @var Collection|OrderAddress[]
     *
     * @ORM\OneToMany(targetEntity=OrderAddress::class, mappedBy="order", cascade={"persist", "remove"})
     */
    private $orderAddresses;

    /**
     * @ORM\OneToMany(targetEntity=OrderCondition::class, mappedBy="order", orphanRemoval=true)
     */
    private $orderConditions;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="order", orphanRemoval=true)
     * @Groups({"default", "order.index", "order.show", "order.items",})
     */
    private $orderItems;

    /**
     * @ORM\OneToMany(targetEntity=RefundDocument::class, mappedBy="order", orphanRemoval=true)
     *
     * @Groups({"order.show"})
     */
    private $refundDocuments;

    /**
     * @ORM\OneToOne(targetEntity=OrderDocument::class, inversedBy="order", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"customer.order.show", "order.show"})
     */
    private $orderDocument;

    /**
     * @ORM\OneToMany(targetEntity=OrderStatusLog::class, mappedBy="order")
     * @Groups({"order.show"})
     * @SerializedName("statusLogs")
     */
    private $orderStatusLogs;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $paidAt;

    /**
     * @var string
     * @Gedmo\Blameable(on="update")
     * @ORM\Column(type="string", nullable=true)
     */
    protected $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity=OrderNote::class, mappedBy="order")
     */
    private $orderNotes;

    /**
     * @ORM\ManyToMany(targetEntity=Promotion::class)
     */
    private $promotions;

    /**
     * @ORM\ManyToOne(targetEntity=PromotionCoupon::class)
     *
     * @Groups({"order.details", "order.index", "order.show"})
     */
    private $promotionCoupon;

    /**
     * @ORM\OneToMany(targetEntity=OrderPromotionDiscount::class, mappedBy="subject", orphanRemoval=true)
     *
     * @Groups({"order.details"})
     */
    private $discounts;

    /**
     * @ORM\Column(type="bigint", options={"default"=0})
     *
     * @Groups({"default", "order.show", "customer.order.index", "customer.order.show"})
     */
    private $discountTotal = 0;

    /**
     * @ORM\OneToMany(targetEntity=RateAndReview::class, mappedBy="order")
     */
    private $rateAndReviews;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     *
     * @Groups({"default", "order.show", "customer.order.index", "customer.order.show"})
     */
    private $promotionLocked = true;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     *
     * @Groups({
     *     "default",
     *     "order.index",
     *     "order.show",
     * })
     */
    private $isLegal = false;

    /**
     * @ORM\OneToMany(targetEntity=OrderLegalAccount::class, mappedBy="order", cascade={"persist", "remove"})
     */
    private $orderLegalAccounts;

    /**
     * @ORM\OneToOne(targetEntity=OrderAffiliator::class, mappedBy="order", cascade={"persist", "remove"})
     */
    private $affiliator;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     */
    private $hasWalletPayment = false;

    /**
     * @ORM\OneToMany(targetEntity=ReturnRequest::class, mappedBy="order")
     */
    private $returnRequests;

    public function __construct()
    {
        $this->shipments = new ArrayCollection();
        $this->orderConditions = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->refundDocuments = new ArrayCollection();
        $this->orderStatusLogs = new ArrayCollection();
        $this->orderNotes = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->discounts = new ArrayCollection();
        $this->rateAndReviews = new ArrayCollection();
        $this->orderAddresses = new ArrayCollection();
        $this->orderLegalAccounts = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function setStatus(string $status): self
    {
        if (! in_array($status, OrderStatus::toArray(), true)) {
            throw new InvalidOrderStatusException();
        }

        $this->status = $status;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
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

    public function getBalanceAmount(): ?int
    {
        return $this->balanceAmount;
    }

    public function setBalanceAmount($balanceAmount): self
    {
        $this->balanceAmount = $balanceAmount;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @throws InvalidPaymentMethodException
     * @throws ReflectionException
     */
    public function setPaymentMethod(string $paymentMethod): self
    {
        if (!in_array($paymentMethod, OrderPaymentMethod::toArray())) {
            throw new InvalidPaymentMethodException('payment method is invalid: ' . $paymentMethod);
        }

        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function hasOfflinePaymentMethod(): bool
    {
        return $this->paymentMethod === OrderPaymentMethod::OFFLINE;
    }

    public function hasOnlinePaymentMethod(): bool
    {
        return ! $this->hasOfflinePaymentMethod();
    }

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection|OrderShipment[]
     */
    public function getShipments(): Collection
    {
        return $this->shipments;
    }

    public function addShipment(OrderShipment $shipment): self
    {
        if (! $this->shipments->contains($shipment)) {
            $this->shipments[] = $shipment;
            $shipment->setOrder($this);
        }

        return $this;
    }

    public function removeShipment(OrderShipment $shipment): self
    {
        if ($this->shipments->contains($shipment)) {
            $this->shipments->removeElement($shipment);
            // set the owning side to null (unless already changed)
            if ($shipment->getOrder() === $this) {
                $shipment->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return OrderAddress|null
     *
     * @Groups({
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.order.show",
     *     "orderShipment.index",
     *     "carrier.inquiry.show",
     * })
     */
    public function getOrderAddress(): ?OrderAddress
    {
        return collect($this->orderAddresses)->first(fn(OrderAddress $oa) => $oa->getIsActive());
    }

    /**
     * @return Collection|OrderAddress[]
     */
    public function getOrderAddresses(): Collection
    {
        return $this->orderAddresses;
    }

    public function addOrderAddress(OrderAddress $orderAddress): self
    {
        $this->orderAddresses->forAll(function (int $i, OrderAddress $oa) {
            $oa->setIsActive(false);

            return true;
        });

        if (!$this->orderAddresses->contains($orderAddress)) {
            $this->orderAddresses[] = $orderAddress;
            $orderAddress->setOrder($this);
        }

        $orderAddress->setIsActive(true);

        return $this;
    }

    public function removeOrderAddress(OrderAddress $orderAddress): self
    {
        return $this;
    }

    /**
     * @return Collection|OrderCondition[]
     */
    public function getOrderConditions(): Collection
    {
        return $this->orderConditions;
    }

    public function addOrderCondition(OrderCondition $orderCondition): self
    {
        if (! $this->orderConditions->contains($orderCondition)) {
            $this->orderConditions[] = $orderCondition;
            $orderCondition->setOrder($this);
        }

        return $this;
    }

    public function removeOrderCondition(OrderCondition $orderCondition): self
    {
        if ($this->orderConditions->contains($orderCondition)) {
            $this->orderConditions->removeElement($orderCondition);
            // set the owning side to null (unless already changed)
            if ($orderCondition->getOrder() === $this) {
                $orderCondition->setOrder(null);
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
        if (! $this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RefundDocument[]
     */
    public function getRefundDocuments(): Collection
    {
        return $this->refundDocuments;
    }

    public function addRefundDocument(RefundDocument $refundDocument): self
    {
        if (! $this->refundDocuments->contains($refundDocument)) {
            $this->refundDocuments[] = $refundDocument;
            $refundDocument->setOrder($this);
        }

        return $this;
    }

    public function removeRefundDocument(RefundDocument $refundDocument): self
    {
        if ($this->refundDocuments->contains($refundDocument)) {
            $this->refundDocuments->removeElement($refundDocument);
            // set the owning side to null (unless already changed)
            if ($refundDocument->getOrder() === $this) {
                $refundDocument->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @throws OrderDocumentNotFoundException
     */
    public function getOrderDocumentOrFail(): OrderDocument
    {
        $orderDocument = $this->getOrderDocument();

        if ($orderDocument === null) {
            throw new OrderDocumentNotFoundException();
        }

        return $orderDocument;
    }

    public function getOrderDocument(): ?OrderDocument
    {
        return $this->orderDocument;
    }

    public function setOrderDocument(OrderDocument $orderDocument): self
    {
        $this->orderDocument = $orderDocument;

        return $this;
    }

    /**
     * @return Collection|OrderStatusLog[]
     */
    public function getOrderStatusLogs(): Collection
    {
        return $this->orderStatusLogs;
    }

    public function addOrderStatusLog(OrderStatusLog $orderStatusLog): self
    {
        if (! $this->orderStatusLogs->contains($orderStatusLog)) {
            $this->orderStatusLogs[] = $orderStatusLog;
            $orderStatusLog->setOrder($this);
        }

        return $this;
    }

    public function removeOrderStatusLog(OrderStatusLog $orderStatusLog): self
    {
        if ($this->orderStatusLogs->contains($orderStatusLog)) {
            $this->orderStatusLogs->removeElement($orderStatusLog);
            // set the owning side to null (unless already changed)
            if ($orderStatusLog->getOrder() === $this) {
                $orderStatusLog->setOrder(null);
            }
        }

        return $this;
    }

    public function getPaidAt(): ?DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(?DateTimeInterface $paidAt): self
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function setUpdatedBy($updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    /**
     * @Groups({"customer.order.index", "customer.order.show","orderShipment.index", "orderShipment.show"})
     *
     * @SerializedName("isPaid")
     */
    public function isPaid(): bool
    {
        return $this->getPaidAt() !== null;
    }

    public function releaseReservedStock(): void
    {
        /** @var OrderItem $orderItem */
        foreach ($this->getOrderItems() as $orderItem) {
            $orderItem->releaseReservedStock();
        }
    }

    public function getOrderNotes(): Collection
    {
        $criteria = Criteria::create()->orderBy(['id' => Criteria::DESC]);

        return $this->orderNotes->matching($criteria);
    }

    public function addOrderNote(OrderNote $orderNote): self
    {
        if (! $this->orderNotes->contains($orderNote)) {
            $this->orderNotes[] = $orderNote;
            $orderNote->setOrder($this);
        }

        return $this;
    }

    public function removeOrderNote(OrderNote $orderNote): self
    {
        if ($this->orderNotes->contains($orderNote)) {
            $this->orderNotes->removeElement($orderNote);
            // set the owning side to null (unless already changed)
            if ($orderNote->getOrder() === $this) {
                $orderNote->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Promotion[]
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): self
    {
        if (! $this->promotions->contains($promotion)) {
            $this->promotions[] = $promotion;
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): self
    {
        $this->promotions->removeElement($promotion);

        return $this;
    }

    public function getPromotionCoupon(): ?PromotionCoupon
    {
        return $this->promotionCoupon;
    }

    public function setPromotionCoupon(?PromotionCoupon $promotionCoupon): self
    {
        $this->promotionCoupon = $promotionCoupon;

        return $this;
    }

    /**
     * @return Collection|PersistentCollection|OrderPromotionDiscount[]
     */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    /**
     * @param PromotionAction $action
     *
     * @return Collection|OrderPromotionDiscount[]
     */
    public function getActionDiscounts(PromotionAction $action): Collection
    {
        $criteria = Criteria::create();
        $expression = Criteria::expr();

        $criteria->andWhere($expression->eq('action', $action));

        return $this->getDiscounts()->matching($criteria);
    }

    public function addDiscount(PromotionDiscount $discount): self
    {
        if (! $this->discounts->contains($discount)) {
            $this->discounts[] = $discount;
            $discount->setSubject($this);
        }

        return $this;
    }

    public function removeDiscount(PromotionDiscount $discount): self
    {
        // set the owning side to null (unless already changed)
        if ($this->discounts->removeElement($discount) && $discount->getSubject() === $this) {
            $discount->setSubject(null);

            if ($discount instanceof OrderPromotionDiscount && $discount->getOrderShipment()) {
                $discount->getOrderShipment()->removeDiscount($discount);
            }
        }

        return $this;
    }

    public function hasPromotion(Promotion $promotion): bool
    {
        return $this->getPromotions()->contains($promotion);
    }

    public function getPromotionSubjectTotal(): int
    {
        return $this->getGrandTotal();
    }

    public function getItemsCount()
    {
        return collect($this->getOrderItems())
            ->map(fn (OrderItem $cartItem) => $cartItem->getQuantity())
            ->sum();
    }

    public function updateTotals(): self
    {
        $grandTotal = 0;
        $subTotal = 0;
        $discountTotal = 0;
        foreach ($this->getOrderItems() as $orderItem) {
            $grandTotal += $orderItem->getGrandTotal();
            $subTotal += $orderItem->getSubtotal();
        }

        foreach ($this->getDiscounts() as $discount) {
            $discountTotal += $discount->getAmount();
        }

        $grandTotal -= $discountTotal;

        $this->setGrandTotal($grandTotal);
        $this->setSubtotal($subTotal);
        $this->setDiscountTotal($discountTotal);

        return $this;
    }

    public function getItems()
    {
        return $this->getOrderItems();
    }

    public function getAddress(): ?OrderAddress
    {
        return $this->getOrderAddress();
    }

    public function getDiscountTotal(): ?string
    {
        return $this->discountTotal;
    }

    public function setDiscountTotal(string $discountTotal): self
    {
        $this->discountTotal = $discountTotal;

        return $this;
    }

    /**
     * @Groups({"customer.order.index", "customer.order.show",})
     *
     * @SerializedName("payable")
     */
    public function getPayable(): int
    {
        $totalPayments = $this->calculateTotalPayments();
        $totalRefunds = $this->calculateTotalRefunds();
        $balance = $totalPayments - $totalRefunds;
        $finalPrice = $this->isCanceled() ? 0 : $this->getOrderDocument()->getAmount();

        return $finalPrice - $balance;
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
        if (! $this->rateAndReviews->contains($rateAndReview)) {
            $this->rateAndReviews[] = $rateAndReview;
            $rateAndReview->setOrder($this);
        }

        return $this;
    }

    public function removeRateAndReview(RateAndReview $rateAndReview): self
    {
        if ($this->rateAndReviews->removeElement($rateAndReview)) {
            // set the owning side to null (unless already changed)
            if ($rateAndReview->getOrder() === $this) {
                $rateAndReview->setOrder(null);
            }
        }

        return $this;
    }

    public function isLockedPromotion(): bool
    {
        return $this->getPromotionLocked();
    }

    public function getPromotionLocked(): bool
    {
        return $this->promotionLocked;
    }

    public function setPromotionLocked(bool $promotionLocked): self
    {
        $this->promotionLocked = $promotionLocked;

        return $this;
    }

    public function getIsLegal(): ?bool
    {
        return $this->isLegal;
    }

    public function setIsLegal(bool $isLegal): self
    {
        $this->isLegal = $isLegal;

        return $this;
    }

    /**
     * @SerializedName("legalAccount")
     *
     * @Groups({
     *     "order.index",
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    public function getLegalAccount(): ?OrderLegalAccount
    {
        $criteria = Criteria::create();

        $criteria->andWhere(Criteria::expr()->eq('isActive', true))
                 ->orderBy(['id' => Criteria::DESC])
                 ->setMaxResults(1);

        return $this->getOrderLegalAccounts()->matching($criteria)->first() ?: null;
    }

    /**
     * @return Collection|OrderLegalAccount[]
     */
    public function getOrderLegalAccounts(): Collection
    {
        return $this->orderLegalAccounts;
    }

    public function addOrderLegalAccount(OrderLegalAccount $orderLegalAccount): self
    {
        if (!$this->orderLegalAccounts->contains($orderLegalAccount)) {
            $this->orderLegalAccounts[] = $orderLegalAccount;
            $orderLegalAccount->setOrder($this);
        }

        return $this;
    }

    public function removeOrderLegalAccount(OrderLegalAccount $orderLegalAccount): self
    {
        if ($this->orderLegalAccounts->removeElement($orderLegalAccount)) {
            // set the owning side to null (unless already changed)
            if ($orderLegalAccount->getOrder() === $this) {
                $orderLegalAccount->setOrder(null);
            }
        }

        return $this;
    }

    public function hasLegalAccount(): bool
    {
        return 0 < $this->orderLegalAccounts->count();
    }

    public function checkStatusEqualsTo(string $status): bool
    {
        return $this->getStatus() === $status;
    }

    public function getAffiliator(): ?OrderAffiliator
    {
        return $this->affiliator;
    }

    public function setAffiliator(OrderAffiliator $affiliator): self
    {
        // set the owning side of the relation if necessary
        if ($affiliator->getOrder() !== $this) {
            $affiliator->setOrder($this);
        }

        $this->affiliator = $affiliator;

        return $this;
    }

    public function getShipmentsCount(): int
    {
        return $this->getShipments()->count();
    }

    public function setHasWalletPayment(bool $hasWalletPayment): self
    {
        $this->hasWalletPayment = $hasWalletPayment;

        return $this;
    }

    public function hasWalletPayment(): bool
    {
        return $this->hasWalletPayment ?? false;
    }

    public function hasOnlinePayment(): bool
    {
        return $this->paymentMethod === OrderPaymentMethod::ONLINE &&
               $this->getPayable() > 0;
    }

    public function calculateWalletPayments(): int
    {
        return
            collect(
                $this
                    ->getOrderDocument()
                    ->getTransactions()
                    ->filter(fn(Transaction $transaction) => $transaction->isSuccessful() && $transaction->isOnWalletGateway())
            )->sum(fn(Transaction $transaction) => $transaction->getAmount());
    }

    public function calculateWalletRefunds(): int
    {
        $sum = 0;
        foreach ($this->getRefundDocuments() as $document) {
            $sum +=
                collect(
                    $document
                        ->getTransactions()
                        ->filter(fn(Transaction $transaction) => $transaction->isSuccessful() && $transaction->isOnWalletGateway())
                )->sum(fn(Transaction $transaction) => $transaction->getAmount());
        }

        return $sum;
    }

    private function calculateTotalPayments(): int
    {
        return
            collect(
                $this
                    ->getOrderDocument()
                    ->getTransactions()
                    ->filter(fn(Transaction $transaction) => $transaction->isSuccessful())
            )->sum(fn(Transaction $transaction) => $transaction->getAmount());
    }

    private function calculateTotalRefunds(): int
    {
        $sum = 0;
        foreach ($this->getRefundDocuments() as $document) {
            $sum +=
                collect(
                    $document
                        ->getTransactions()
                        ->filter(fn(Transaction $transaction) => $transaction->isSuccessful())
                )->sum(fn(Transaction $transaction) => $transaction->getAmount());
        }

        return $sum;
    }

    public function isCanceled(): bool
    {
        return in_array(
            $this->status,
            [OrderStatus::CANCELED, OrderStatus::CANCELED_SYSTEM]
        );
    }

    /**
     * @return ReturnRequest[]
     */
    public function getReturnRequests(): Collection
    {
        return $this->returnRequests;
    }
}
