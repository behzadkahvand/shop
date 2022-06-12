<?php

namespace App\Entity;

use App\Repository\TransactionMetaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="transaction_metas")
 * @ORM\Entity(repositoryClass=TransactionMetaRepository::class)
 */
class TransactionMeta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="`key`", type="string", length=255)
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.show.driver"
     * })
     */
    private $key;

    /**
     * @var array<string>
     *
     * @ORM\Column(type="json")
     *
     * @Groups({
     *     "orderShipment.show",
     *     "orderShipment.show.driver"
     * })
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=Transaction::class, inversedBy="transactionMetas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }
}
