<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\AccountRepository;
use App\Validator as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="customer_accounts")
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 */
class Account
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"customer.read"})
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Customer::class, inversedBy="account")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"default", "customer.read", "customer.customer.read"})
     */
    private $bank;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"default", "customer.read", "customer.customer.read", "customer.auth.profile"})
     */
    private $shebaNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @AppAssert\BankingCard(groups={"customer.customer.update"})
     *
     * @Groups({"default", "customer.read", "customer.customer.read", "customer.auth.profile"})
     */
    private $cardNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"default", "customer.read", "customer.customer.read"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"default", "customer.read", "customer.customer.read"})
     */
    private $lastName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getShebaNumber(): ?string
    {
        return $this->shebaNumber;
    }

    public function setShebaNumber(?string $shebaNumber): self
    {
        $this->shebaNumber = $shebaNumber;

        return $this;
    }

    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(?string $cardNumber): self
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(string $bank): self
    {
        $this->bank = $bank;

        return $this;
    }
}
