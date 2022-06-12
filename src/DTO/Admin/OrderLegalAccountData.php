<?php

namespace App\DTO\Admin;

use App\Entity\City;
use App\Entity\Province;

class OrderLegalAccountData
{
    private Province $province;

    private City $city;

    private string $organizationName;

    private int $economicCode;

    private string $nationalId;

    private string $registrationId;

    private string $phoneNumber;

    /**
     * @return Province
     */
    public function getProvince(): Province
    {
        return $this->province;
    }

    /**
     * @param Province $province
     * @return OrderLegalAccountData
     */
    public function setProvince(Province $province): OrderLegalAccountData
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
     * @return OrderLegalAccountData
     */
    public function setCity(City $city): OrderLegalAccountData
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
     * @return OrderLegalAccountData
     */
    public function setOrganizationName(string $organizationName): OrderLegalAccountData
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
     * @return OrderLegalAccountData
     */
    public function setEconomicCode(int $economicCode): OrderLegalAccountData
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
     * @return OrderLegalAccountData
     */
    public function setNationalId(string $nationalId): OrderLegalAccountData
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
     * @return OrderLegalAccountData
     */
    public function setRegistrationId(string $registrationId): OrderLegalAccountData
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
     * @return OrderLegalAccountData
     */
    public function setPhoneNumber(string $phoneNumber): OrderLegalAccountData
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
}
