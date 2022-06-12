<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\CustomerLegalAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="customer_legal_accounts")
 * @ORM\Entity(repositoryClass=CustomerLegalAccountRepository::class)
 */
class CustomerLegalAccount
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Customer::class, inversedBy="legalAccount", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $organizationName;

    /**
     * @ORM\Column(type="string", length=16)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $economicCode;

    /**
     * @ORM\Column(type="string", length=60)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $nationalId;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $registrationId;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=12)
     * @Groups({
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     * })
     */
    private $phoneNumber;

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

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(string $organizationName): self
    {
        $this->organizationName = $organizationName;

        return $this;
    }

    public function getEconomicCode(): ?string
    {
        return $this->economicCode;
    }

    public function setEconomicCode($economicCode): self
    {
        $this->economicCode = $economicCode;

        return $this;
    }

    public function getNationalId(): ?string
    {
        return $this->nationalId;
    }

    public function setNationalId(string $nationalId): self
    {
        $this->nationalId = $nationalId;

        return $this;
    }

    public function getRegistrationId(): ?string
    {
        return $this->registrationId;
    }

    public function setRegistrationId(string $registrationId): self
    {
        $this->registrationId = $registrationId;

        return $this;
    }

    public function getProvince(): ?Province
    {
        return $this->province;
    }

    public function setProvince(?Province $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
