<?php

namespace App\Entity;

use App\Dictionary\TransactionStatus;
use App\Entity\Common\Timestampable;
use App\Repository\TransactionRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="transactions")
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"customer.order.show", "order.show"})
     */
    private $status = TransactionStatus::NEW;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"customer.order.show", "order.show"})
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"customer.order.show", "order.show"})
     */
    private $gateway;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referenceNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"order.show"})
     */
    private $trackingNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identifier;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"order.show"})
     */
    private $paidAt;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @ORM\OneToMany(targetEntity=TransactionMeta::class, mappedBy="transaction", orphanRemoval=true)
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.show.driver"
     * })
     */
    private $transactionMetas;

    /**
     * @ORM\OneToOne(targetEntity=OrderShipment::class, cascade={"persist", "remove"})
     */
    private $orderShipment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    public function __construct()
    {
        $this->transactionMetas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(string $gateway): self
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?string $referenceNumber): self
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): self
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(int $identifier): self
    {
        $this->identifier = $identifier;

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

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function isVerified(): bool
    {
        return in_array($this->status, [TransactionStatus::SUCCESS, TransactionStatus::FAILED], true);
    }

    /**
     * @return Collection|TransactionMeta[]
     */
    public function getTransactionMetas(): Collection
    {
        return $this->transactionMetas;
    }

    public function addTransactionMeta(TransactionMeta $transactionMeta): self
    {
        if (!$this->transactionMetas->contains($transactionMeta)) {
            $this->transactionMetas[] = $transactionMeta;
            $transactionMeta->setTransaction($this);
        }

        return $this;
    }

    public function removeTransactionMeta(TransactionMeta $transactionMeta): self
    {
        if ($this->transactionMetas->removeElement($transactionMeta)) {
            // set the owning side to null (unless already changed)
            if ($transactionMeta->getTransaction() === $this) {
                $transactionMeta->setTransaction(null);
            }
        }

        return $this;
    }

    public function getOrderShipment(): ?OrderShipment
    {
        return $this->orderShipment;
    }

    public function setOrderShipment(?OrderShipment $orderShipment): self
    {
        $this->orderShipment = $orderShipment;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function isOnWalletGateway(): bool
    {
        return $this->gateway === Wallet::GATEWAY_NAME;
    }

    public function isSuccessful(): bool
    {
        return $this->status === TransactionStatus::SUCCESS;
    }
}
