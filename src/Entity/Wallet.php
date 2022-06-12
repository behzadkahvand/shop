<?php

namespace App\Entity;

use App\DTO\Wallet\TransferRequest;
use App\Entity\Common\Timestampable;
use App\Exceptions\Wallet\InvalidWalletTransactionException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OpenApi\Annotations as OA;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\WalletRepository;

/**
 * @ORM\Table(name="wallets")
 * @ORM\Entity(repositoryClass=WalletRepository::class)
 */
class Wallet
{
    use Timestampable;

    public const GATEWAY_NAME = 'WALLET';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"default", "wallet.show", "customer.read"})
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=WalletHistory::class, mappedBy="wallet", orphanRemoval=true, cascade={"persist"})
     */
    private $histories;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"default", "wallet.show", "customer.auth.profile", "customer.read", "customer.customer.read"})
     */
    private $balance;

    /**
     * @ORM\Column(type="boolean", options={"default"=0})
     *
     * @Groups({"default", "wallet.show", "customer.auth.profile", "customer.read", "customer.customer.read"})
     *
     */
    private $isFrozen = false;

    public function __construct()
    {
        $this->histories = new ArrayCollection();
        $this->balance = 0;
    }

    /**
     * @throws InvalidWalletTransactionException
     */
    public function deposit(TransferRequest $transferRequest): void
    {
        $this->validateNotFrozen();

        $amount = $transferRequest->getAmount();
        if ($amount === 0) {
            return;
        }

        if ($amount < 0) {
            throw new InvalidWalletTransactionException('Can not deposit a negative amount into wallet!');
        }

        $history = new WalletHistory();
        $history->setAmount($amount)
                ->setBeforeAmount($this->balance)
                ->setAfterAmount($this->balance + $amount)
                ->setType(WalletHistory::DEPOSIT)
                ->setReason($transferRequest->getReason())
                ->setReferenceId($transferRequest->getReferenceId())
                ->setOrder($transferRequest->getOrder())
                ->setWallet($this);

        $this->histories->add($history);

        $this->balance += $amount;
    }

    /**
     * @throws InvalidWalletTransactionException
     */
    public function withdraw(TransferRequest $transferRequest): void
    {
        $this->validateNotFrozen();

        $amount = $transferRequest->getAmount();
        if ($amount === 0) {
            return;
        }

        if ($amount < 0) {
            throw new InvalidWalletTransactionException('Can not withdraw a negative amount from wallet!');
        }

        if ($this->balance < $amount) {
            throw new InvalidWalletTransactionException('Insufficient wallet balance.');
        }

        $history = new WalletHistory();
        $history->setAmount($amount)
            ->setBeforeAmount($this->balance)
            ->setAfterAmount($this->balance - $amount)
            ->setType(WalletHistory::WITHDRAW)
            ->setReason($transferRequest->getReason())
            ->setReferenceId($transferRequest->getReferenceId())
            ->setOrder($transferRequest->getOrder())
            ->setWallet($this);

        $this->histories->add($history);

        $this->balance -= $amount;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    /**
     * @return WalletHistory[]
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isFrozen(): bool
    {
        return $this->isFrozen;
    }

    public function setIsFrozen(bool $isFrozen): self
    {
        $this->isFrozen = $isFrozen;

        return $this;
    }

    public function freeze(): void
    {
        $this->setIsFrozen(true);
    }

    /**
     * @throws InvalidWalletTransactionException
     */
    private function validateNotFrozen(): void
    {
        if ($this->isFrozen()) {
            throw new InvalidWalletTransactionException('Wallet is frozen');
        }
    }
}
