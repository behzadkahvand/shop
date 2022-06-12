<?php

namespace App\Entity;

use App\Dictionary\RateAndReviewStatus;
use App\Dictionary\RateAndReviewSuggestion;
use App\Entity\Common\Timestampable;
use App\Repository\RateAndReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=RateAndReviewRepository::class)
 *
 * @ORM\Table(name="rate_and_reviews", uniqueConstraints={
 *     @UniqueConstraint(name="customer_product", columns={"customer_id", "product_id"})
 * })
 */
class RateAndReview
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "customer.rateAndReview.update",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "admin.rateAndReview.update",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "customer.rateAndReview.update",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "customer.rateAndReview.update",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $suggestion = RateAndReviewSuggestion::NO_COMMENT;

    /**
     * @ORM\Column(type="smallint", options={"unsigned"=true})
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $rate = 5;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $anonymous = false;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "customer.rateAndReview.index",
     *     "customer.rateAndReview.update",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "admin.rateAndReview.update",
     * })
     */
    private $status = RateAndReviewStatus::WAIT_FOR_ACCEPT;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="rateAndReviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="rateAndReviews")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "customer.rateAndReview.products",
     *     "customer.rateAndReview.index",
     * })
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Inventory::class, inversedBy="rateAndReviews")
     */
    private $inventory;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="rateAndReviews")
     *
     * @Groups({
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $order;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "admin.rateAndReview.update",
     * })
     */
    private $pin = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }

    public function setSuggestion(?string $suggestion): self
    {
        $this->suggestion = $suggestion;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getAnonymous(): ?bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): self
    {
        $this->anonymous = $anonymous;

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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

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

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): self
    {
        $this->inventory = $inventory;

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
     * @SerializedName("seller")
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    public function getSeller(): ?Seller
    {
        return $this->getInventory()?->getSeller();
    }

    /**
     * @SerializedName("customerName")
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     * })
     */
    public function getCustomerName(): string
    {
        return $this->anonymous ? 'کاربر تیمچه' : $this->getCustomer()->getFullName();
    }

    /**
     * @SerializedName("customerName")
     *
     * @Groups({
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    public function getCustomerNameInBackOffice(): string
    {
        return $this->getCustomer()->getFullName();
    }

    /**
     * @SerializedName("isBuyer")
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    public function getIsBuyer(): bool
    {
        return $this->getOrder() !== null;
    }

    /**
     * @SerializedName("productVariant")
     *
     * @Groups({
     *     "customer.product.rateAndReview.index",
     *     "customer.rateAndReview.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    public function getProductVariant(): ?ProductVariant
    {
        return $this->getInventory()?->getVariant();
    }

    /**
     * @return bool
     */
    public function isAccepted(): bool
    {
        return RateAndReviewStatus::ACCEPTED === $this->getStatus();
    }

    public function getPin(): ?bool
    {
        return $this->pin;
    }

    public function setPin(bool $pin): self
    {
        $this->pin = $pin;

        return $this;
    }
}
