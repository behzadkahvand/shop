<?php

namespace App\Entity;

use App\Repository\ReturnRequestItemRepository;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestStatus;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @ORM\Table(name="return_request_items")
 * @ORM\Entity(repositoryClass=ReturnRequestItemRepository::class)
 */
class ReturnRequestItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"return_request.show", "return_request.index"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ReturnRequest::class, inversedBy="items")
     *
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity=OrderItem::class, inversedBy="returnRequestItems")
     *
     * @Groups({"return_request.show", "return_request.index"})
     */
    private $orderItem;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"return_request.show"})
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=ReturnReason::class)
     *
     * @Groups({"return_request.show"})
     */
    private $returnReason;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     *
     * @Groups({"return_request.show"})
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"return_request.show", "return_request.index"})
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"return_request.show"})
     */
    private $isReturnable;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"return_request.show"})
     */
    private $refundAmount;

    /**
     * @ORM\Column(type="json")
     * @OA\Property(type="array", @OA\Items(type="string"))
     *
     * @Groups({"return_request.show"})
     */
    private array $data = [];

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     *
     * @OA\Property(example="2021-04-16 11:11:11"),
     *
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d h:i:s'])]
    protected $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     *
     * @OA\Property(example="2021-04-16 11:11:11"),
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d h:i:s'])]
    protected $updatedAt;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="update")
     *
     */
    protected $updatedBy;

    public function __construct()
    {
        $this->setStatus(ReturnRequestStatus::APPROVED);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRequest(): ?ReturnRequest
    {
        return $this->request;
    }

    public function setRequest(ReturnRequest $request): self
    {
        $this->request = $request;

        return $this;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getReturnReason(): ?ReturnReason
    {
        return $this->returnReason;
    }

    public function setReturnReason(ReturnReason $returnReason): self
    {
        $this->returnReason = $returnReason;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status, array $context = []): self
    {
        $this->status = $status;
        $this->data = array_merge($this->data, $context);

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getIsReturnable(): bool
    {
        return $this->isReturnable;
    }

    public function setIsReturnable(bool $isReturnable): self
    {
        $this->isReturnable = $isReturnable;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setRefundAmount(int $refundAmount): self
    {
        $this->refundAmount = $refundAmount;

        return $this;
    }

    public function getRefundAmount(): int
    {
        return $this->refundAmount;
    }

    public function isRefunded(): bool
    {
        return $this->getStatus() === ReturnRequestStatus::REFUNDED;
    }

    public function isApproved(): bool
    {
        return $this->getStatus() === ReturnRequestStatus::APPROVED;
    }

    public function hasEarlierStatusThan(ReturnRequestItem $other): bool
    {
        return ReturnRequestStatus::indexOf(($this->getStatus())) <
               ReturnRequestStatus::indexOf($other->getStatus());
    }

    public function isCanceled(): bool
    {
        return $this->getStatus() === ReturnRequestStatus::CANCELED;
    }

    public function isRejected(): bool
    {
        return $this->getStatus() === ReturnRequestStatus::REJECTED;
    }
}
