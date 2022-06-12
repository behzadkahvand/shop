<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use DateTime;
use Invalidargumentexception;
use OpenApi\Annotations as OA;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use App\Repository\WalletHistoryRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(name="wallet_histories", uniqueConstraints={
 *     @UniqueConstraint(name="reference_type", columns={"reference_id", "type"})
 * })
 * @ORM\Entity(repositoryClass=WalletHistoryRepository::class)
 */
class WalletHistory
{
    public const DEPOSIT = 'deposit';
    public const WITHDRAW = 'withdraw';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Wallet::class, inversedBy="histories", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $wallet;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"wallet_history.show"})
     */
    private $amount;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"wallet_history.show"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class)
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({"wallet_history.show"})
     */
    private $order;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"wallet_history.show"})
     */
    private $referenceId;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"wallet_history.show"})
     */
    private $reason;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true, "default"=0})
     *
     * @Groups({"wallet_history.show"})
     */
    private $beforeAmount = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true, "default"=0})
     *
     * @Groups({"wallet_history.show"})
     */
    private $afterAmount = 0;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     * @OA\Property(example="2018-04-16 11:11:11"),
     *
     * @Groups({"wallet_history.show"})
     *
     */
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    private $createdAt;

    public function setAmount(int $amount): self
    {
        if ($amount < 0) {
            throw new \Exception('negative amount');
        }

        $this->amount = $amount;

        return $this;
    }

    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, [self::DEPOSIT, self::WITHDRAW])) {
            throw new Invalidargumentexception();
        }

        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReferenceId($referenceId): self
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    public function setReason($reason): self
    {
        $this->reason = $reason;

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

    public function getBeforeAmount(): int
    {
        return $this->beforeAmount;
    }

    public function setBeforeAmount(int $beforeAmount): self
    {
        $this->beforeAmount = $beforeAmount;

        return $this;
    }

    public function getAfterAmount(): int
    {
        return $this->afterAmount;
    }

    public function setAfterAmount(int $afterAmount): self
    {
        $this->afterAmount = $afterAmount;

        return $this;
    }
}
