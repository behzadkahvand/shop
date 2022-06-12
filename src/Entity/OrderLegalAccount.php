<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\OrderLegalAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="order_legal_accounts")
 * @ORM\Entity(repositoryClass=OrderLegalAccountRepository::class)
 */
class OrderLegalAccount
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CustomerLegalAccount::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $customerLegalAccount;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderLegalAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $organizationName;

    /**
     * @ORM\Column(type="string", length=16)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $economicCode;

    /**
     * @ORM\Column(type="string", length=60)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $nationalId;

    /**
     * @ORM\Column(type="string", length=30)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $registrationId;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=12)
     *
     * @Groups({
     *     "order.show",
     *     "order.legal.account.store",
     * })
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerLegalAccount(): ?CustomerLegalAccount
    {
        return $this->customerLegalAccount;
    }

    public function setCustomerLegalAccount(?CustomerLegalAccount $customerLegalAccount): self
    {
        $this->customerLegalAccount = $customerLegalAccount;

        return $this;
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

    public function setEconomicCode(string $economicCode): self
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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
