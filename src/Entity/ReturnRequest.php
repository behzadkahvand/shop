<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\ReturnRequestRepository;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestStatus;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @ORM\Table(name="return_requests")
 * @ORM\Entity(repositoryClass=ReturnRequestRepository::class)
 */
class ReturnRequest
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"return_request.index", "return_request.show"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="returnRequests")
     *
     * @Groups({"return_request.index", "return_request.show"})
     */
    private $order;

    /**
     * @ORM\OneToMany(targetEntity=ReturnRequestItem::class, mappedBy="request", cascade={"persist"})
     *
     * @Groups({"return_request.index", "return_request.show"})
     */
    private $items;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"return_request.show", "return_request.index"})
     */
    private $status = ReturnRequestStatus::APPROVED;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"return_request.show", "return_request.index"})
     */
    private $customerAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"return_request.show", "return_request.index"})
     */
    private $driverMobile;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     *
     * @OA\Property(example="2021-04-16 11:11:11"),
     *
     * @Groups({"return_request.index", "return_request.show"})
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    protected $returnDate;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return ?ReturnRequestItem[]
     */
    public function getItems(): ?Collection
    {
        return $this->items;
    }

    public function addItem(ReturnRequestItem $item): self
    {
        $item->setRequest($this);
        $this->items->add($item);

        return $this;
    }

    public function removeItem(ReturnRequestItem $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function getReturnDate(): ?DateTime
    {
        return $this->returnDate;
    }

    public function setReturnDate(?DateTime $returnDate): self
    {
        $this->returnDate = $returnDate;

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

    public function getCustomerAddress(): ?string
    {
        return $this->customerAddress;
    }

    public function setCustomerAddress(?string $customerAddress): self
    {
        $this->customerAddress = $customerAddress;

        return $this;
    }

    public function getDriverMobile(): ?string
    {
        return $this->driverMobile;
    }

    public function setDriverMobile(?string $driverMobile): self
    {
        $this->driverMobile = $driverMobile;

        return $this;
    }

    /**
     * @return bool
     * returns true if status has changed and returns false if not
     */
    public function updateStatus(): bool
    {
        if ($this->allItemsAreCanceled()) {
            $status = ReturnRequestStatus::CANCELED;
        } elseif ($this->allItemsAreRejected()) {
            $status = ReturnRequestStatus::REJECTED;
        } else {
            $status = $this->findItemWithEarliestStatus()->getStatus();
        }

        if ($this->status === $status) {
            return false;
        }

        $this->status = $status;

        return true;
    }

    public function findItemWithEarliestStatus(): ReturnRequestItem
    {
        $items = $this->getItems()->filter(
            fn(ReturnRequestItem $item): bool => !$item->isCanceled() && !$item->isRejected()
        );

        $result = $items->first();
        foreach ($items as $item) {
            if ($item->hasEarlierStatusThan($result)) {
                $result = $item;
            }
        }

        return $result;
    }

    private function allItemsAreCanceled(): bool
    {
        return $this->getItems()->forAll(
            fn(int $index, ReturnRequestItem $item): bool => $item->isCanceled()
        );
    }

    private function allItemsAreRejected(): bool
    {
        return $this->getItems()->forAll(
            fn(int $index, ReturnRequestItem $item): bool => $item->isRejected()
        );
    }
}
