<?php

namespace App\DTO\Customer;

use App\Entity\City;
use App\Entity\Customer;
use App\Entity\District;
use App\Entity\Province;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;

class CustomerAddressData
{
    private Customer $customer;

    private AbstractPoint $location;

    private string $fullAddress;

    private string $postalCode;

    private int $number;

    private Province $province;

    private City $city;

    private ?District $district = null;

    private bool $myAddress = false;

    private ?string $name;

    private ?string $family;

    private ?string $nationalCode;

    private ?string $mobile;

    private ?int $unit = null;

    private ?string $floor = null;

    private bool $isForeigner = false;

    private ?string $pervasiveCode = null;

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     *
     * @return CustomerAddressData
     */
    public function setCustomer(Customer $customer): CustomerAddressData
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return AbstractPoint
     */
    public function getLocation(): AbstractPoint
    {
        return $this->location;
    }

    /**
     * @param AbstractPoint $location
     *
     * @return CustomerAddressData
     */
    public function setLocation(AbstractPoint $location): CustomerAddressData
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullAddress(): string
    {
        return $this->fullAddress;
    }

    /**
     * @param string $fullAddress
     *
     * @return CustomerAddressData
     */
    public function setFullAddress(string $fullAddress): CustomerAddressData
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return CustomerAddressData
     */
    public function setPostalCode(string $postalCode): CustomerAddressData
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return CustomerAddressData
     */
    public function setNumber(int $number): CustomerAddressData
    {
        $this->number = $number;

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
     *
     * @return CustomerAddressData
     */
    public function setProvince(Province $province): CustomerAddressData
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
     *
     * @return CustomerAddressData
     */
    public function setCity(City $city): CustomerAddressData
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return District|null
     */
    public function getDistrict(): ?District
    {
        return $this->district;
    }

    /**
     * @param District|null $district
     *
     * @return CustomerAddressData
     */
    public function setDistrict(?District $district): CustomerAddressData
    {
        $this->district = $district;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMyAddress(): bool
    {
        return $this->myAddress;
    }

    /**
     * @param bool $myAddress
     *
     * @return CustomerAddressData
     */
    public function setMyAddress(bool $myAddress): CustomerAddressData
    {
        $this->myAddress = $myAddress;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return CustomerAddressData
     */
    public function setName(?string $name): CustomerAddressData
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUnit(): ?int
    {
        return $this->unit;
    }

    /**
     * @param int|null $unit
     *
     * @return CustomerAddressData
     */
    public function setUnit(?int $unit): CustomerAddressData
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFloor(): ?string
    {
        return $this->floor;
    }

    /**
     * @param string|null $floor
     *
     * @return CustomerAddressData
     */
    public function setFloor(?string $floor): CustomerAddressData
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFamily(): ?string
    {
        return $this->family;
    }

    /**
     * @param string|null $family
     *
     * @return CustomerAddressData
     */
    public function setFamily(?string $family): CustomerAddressData
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNationalCode(): ?string
    {
        return $this->nationalCode;
    }

    /**
     * @param string|null $nationalCode
     *
     * @return CustomerAddressData
     */
    public function setNationalCode(?string $nationalCode): CustomerAddressData
    {
        $this->nationalCode = $nationalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param string|null $mobile
     *
     * @return CustomerAddressData
     */
    public function setMobile(?string $mobile): CustomerAddressData
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForeigner(): bool
    {
        return $this->isForeigner;
    }

    /**
     * @param bool $isForeigner
     */
    public function setIsForeigner(bool $isForeigner): void
    {
        $this->isForeigner = $isForeigner;
    }

    /**
     * @return string|null
     */
    public function getPervasiveCode(): ?string
    {
        return $this->pervasiveCode;
    }

    /**
     * @param string|null $pervasiveCode
     */
    public function setPervasiveCode(?string $pervasiveCode): void
    {
        $this->pervasiveCode = $pervasiveCode;
    }
}
