<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\DocumentRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="documents")
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ORM\DiscriminatorColumn(name="document_type", type="string", length=32)
 * @ORM\DiscriminatorMap({"order" = "App\Entity\OrderDocument", "refund" = "App\Entity\RefundDocument"})
 * @ORM\InheritanceType("JOINED")
 */
abstract class Document
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8)
     *
     * @Groups({"order.show"})
     * @SerializedName("transactionType")
     */
    private $type;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     *
     * @Groups({"order.show"})
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"order.show"})
     */
    private $completedAt;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="document", orphanRemoval=true)
     *
     * @Groups({"customer.order.show", "order.show"})
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    protected function setType(string $type): self
    {
        $this->type = $type;

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

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setDocument($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getDocument() === $this) {
                $transaction->setDocument(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"order.show"})
     * @SerializedName("type")
     */
    public function getDType(): string
    {
        if ($this instanceof OrderDocument) {
            return 'order';
        }

        if ($this instanceof RefundDocument) {
            return 'refund';
        }

        throw new \RuntimeException('Invalid document type detected');
    }

    public function newTransaction(): Transaction
    {
        $transaction = new Transaction();

        $this->addTransaction($transaction);

        return $transaction;
    }
}
