<?php

namespace App\Response\Presenter;

use App\Entity\Account;
use App\Entity\Customer;
use App\Entity\Wallet;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

final class AuthMePresenter
{
    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?string $name;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?string $family;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?string $email;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private string $gender;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?DateTimeInterface $birthday;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?string $mobile;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?string $nationalNumber;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?string $pervasiveCode;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?Account $account;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private bool $isProfileCompleted;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private bool $isProfileLegal;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private bool $isForeigner;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private int $cartItemsCount;

    /**
     * @Groups({"customer.auth.profile"})
     */
    private ?Wallet $wallet;


    public function __construct(Customer $user)
    {
        $this->name = $user->getName();
        $this->family = $user->getFamily();
        $this->email = $user->getEmail();
        $this->gender = $user->getGender();
        $this->birthday = $user->getBirthday();
        $this->mobile = $user->getMobile();
        $this->nationalNumber = $user->getNationalNumber();
        $this->pervasiveCode = $user->getPervasiveCode();
        $this->account = $user->getAccount();
        $this->isProfileCompleted = $user->isProfileCompleted();
        $this->isProfileLegal = $user->isProfileLegal();
        $this->isForeigner = $user->getIsForeigner();
        $this->cartItemsCount = $this->cartItemsCount($user);
        $this->wallet = $user->getWallet();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function getNationalNumber(): ?string
    {
        return $this->nationalNumber;
    }

    public function getPervasiveCode(): ?string
    {
        return $this->pervasiveCode;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function isProfileCompleted(): bool
    {
        return $this->isProfileCompleted;
    }

    public function isProfileLegal(): bool
    {
        return $this->isProfileLegal;
    }

    public function isForeigner(): bool
    {
        return $this->isForeigner;
    }

    public function getCartItemsCount(): int
    {
        return $this->cartItemsCount;
    }

    private function cartItemsCount(Customer $user): int
    {
        $cart = $user->getCart();

        return $cart === null ? 0 : $cart->getItemsCount();
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }
}
