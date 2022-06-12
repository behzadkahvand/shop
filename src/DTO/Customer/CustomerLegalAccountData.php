<?php

namespace App\DTO\Customer;

use App\Entity\City;
use App\Entity\Customer;
use App\Entity\Province;

class CustomerLegalAccountData
{
    private Customer $customer;

    private Province $province;

    private City $city;

    private string $organizationName;

    private int $economicCode;

    private string $nationalId;

    private string $registrationId;

    private string $phoneNumber;

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     * @return CustomerLegalAccountData
     */
    public function setCustomer(Customer $customer): CustomerLegalAccountData
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return Province
     */
    public function getProvince(): Province
    {
        return $this->province;
    }

    /**
     * @param Province $province
     * @return CustomerLegalAccountData
     */
    public function setProvince(Province $province): CustomerLegalAccountData
    {
        $this->province = $province;
        return $this;
    }

    /**
     * @return City
     */
    public function getCity(): City
    {
        return $this->city;
    }

    /**
     * @param City $city
     * @return CustomerLegalAccountData
     */
    public function setCity(City $city): CustomerLegalAccountData
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationName(): string
    {
        return $this->organizationName;
    }

    /**
     * @param string $organizationName
     * @return CustomerLegalAccountData
     */
    public function setOrganizationName(string $organizationName): CustomerLegalAccountData
    {
        $this->organizationName = $organizationName;
        return $this;
    }

    /**
     * @return int
     */
    public function getEconomicCode(): int
    {
        return $this->economicCode;
    }

    /**
     * @param int $economicCode
     * @return CustomerLegalAccountData
     */
    public function setEconomicCode(int $economicCode): CustomerLegalAccountData
    {
        $this->economicCode = $economicCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getNationalId(): string
    {
        return $this->nationalId;
    }

    /**
     * @param string $nationalId
     * @return CustomerLegalAccountData
     */
    public function setNationalId(string $nationalId): CustomerLegalAccountData
    {
        $this->nationalId = $nationalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationId(): string
    {
        return $this->registrationId;
    }

    /**
     * @param string $registrationId
     * @return CustomerLegalAccountData
     */
    public function setRegistrationId(string $registrationId): CustomerLegalAccountData
    {
        $this->registrationId = $registrationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return CustomerLegalAccountData
     */
    public function setPhoneNumber(string $phoneNumber): CustomerLegalAccountData
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
}
