<?php

namespace App\Tests\Controller\Customer;

use App\Entity\City;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Province;
use App\Repository\CityRepository;
use App\Repository\CustomerAddressRepository;
use App\Repository\CustomerRepository;
use App\Repository\DistrictRepository;
use App\Repository\ProvinceRepository;
use App\Tests\Controller\BaseControllerTestCase;

class CustomerAddressControllerTest extends BaseControllerTestCase
{
    protected ?CustomerAddressRepository $customerAddressRepo;

    protected ?CustomerAddress $customerAddress;

    protected ?Province $province;

    protected ?City $city;

    protected ?District $district;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAddressRepo = $this->getService(CustomerAddressRepository::class);

        $this->customerAddress = $this->customerAddressRepo->findOneBy([]);

        $provinceRepo = $this->getService(ProvinceRepository::class);

        $this->province = $provinceRepo->findOneBy([]);

        $cityRepo = $this->getService(CityRepository::class);

        $this->city = $cityRepo->findOneBy([]);

        $districtRepo = $this->getService(DistrictRepository::class);

        $this->district = $districtRepo->findOneBy([]);
    }

    protected function tearDown(): void
    {
        unset($this->customerAddressRepo, $this->customerAddress, $this->province, $this->city, $this->district);

        parent::tearDown();
    }

    public function testItCanCreateCustomerAddressWhenMyAddressIsTrue()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/customer-addresses",
            [
                "location"    => ["lat" => 35.23211, "long" => 51.55641],
                "fullAddress" => "Test2",
                "city"        => $this->city->getId(),
                "province"    => $this->province->getId(),
                "unit"        => 1,
                "floor"       => "اول",
                "district"    => $this->district->getId(),
                "myAddress"   => 1,
                "postalCode"  => 1520960063,
                "number"      => 44,
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('Customer address is added successfully!', $response['message']);

        $result = $response['results'];

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('fullAddress', $result);
        self::assertArrayHasKey('postalCode', $result);
        self::assertArrayHasKey('isDefault', $result);
        self::assertArrayHasKey('coordinates', $result);
        self::assertArrayHasKey('lat', $result['coordinates']);
        self::assertArrayHasKey('long', $result['coordinates']);
        self::assertArrayHasKey('number', $result);
        self::assertArrayHasKey('unit', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('family', $result);
        self::assertArrayHasKey('nationalCode', $result);
        self::assertArrayHasKey('mobile', $result);
        self::assertArrayHasKey('province', $result);
        self::assertArrayHasKey('id', $result['province']);
        self::assertArrayHasKey('code', $result['province']);
        self::assertArrayHasKey('name', $result['province']);
        self::assertArrayHasKey('city', $result);
        self::assertArrayHasKey('id', $result['city']);
        self::assertArrayHasKey('name', $result['city']);
        self::assertArrayHasKey('district', $result);
        self::assertArrayHasKey('id', $result['district']);
        self::assertArrayHasKey('name', $result['district']);
    }

    public function testItCanCreateForeignerCustomerAddressWithoutPervasiveCode()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/customer-addresses",
            [
                "location"     => ["lat" => 35.23211, "long" => 51.55641],
                "fullAddress"  => "Test2",
                "city"         => $this->city->getId(),
                "province"     => $this->province->getId(),
                "unit"         => 1,
                "floor"        => "1",
                "district"     => $this->district->getId(),
                "myAddress"    => 0,
                "postalCode"   => 1520960063,
                "number"       => 44,
                "name"         => 'John',
                "family"       => 'Doe',
                "mobile"       => '09011000001',
                "nationalCode" => '800008200',
                'isForeigner'  => true,
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $customerAddress = $this->lastEntity(CustomerAddress::class);
        self::assertTrue($customerAddress->getIsForeigner());
        self::assertNull($customerAddress->getPervasiveCode());
    }

    public function testItCanCreateForeignerCustomerAddressWithPervasiveCode()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/customer-addresses",
            [
                "location"      => ["lat" => 35.23211, "long" => 51.55641],
                "fullAddress"   => "Test2",
                "city"          => $this->city->getId(),
                "province"      => $this->province->getId(),
                "unit"          => 1,
                "floor"         => "1",
                "district"      => $this->district->getId(),
                "myAddress"     => 0,
                "postalCode"    => 1520960063,
                "number"        => 44,
                "name"          => 'John',
                "family"        => 'Doe',
                "mobile"        => '09011000001',
                "nationalCode"  => '800008200',
                'isForeigner'   => true,
                'pervasiveCode' => '1234567890123456'
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $customerAddress = $this->lastEntity(CustomerAddress::class);
        self::assertTrue($customerAddress->getIsForeigner());
        self::assertNotNull($customerAddress->getPervasiveCode());
    }

    public function testItCanFailValidationDueToBigLengthOfPervasiveCode()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/customer-addresses",
            [
                "location"      => ["lat" => 35.23211, "long" => 51.55641],
                "fullAddress"   => "Test2",
                "city"          => $this->city->getId(),
                "province"      => $this->province->getId(),
                "unit"          => 1,
                "floor"         => "1",
                "district"      => $this->district->getId(),
                "myAddress"     => 0,
                "postalCode"    => 1520960063,
                "number"        => 44,
                "name"          => 'John',
                "family"        => 'Doe',
                "mobile"        => '09011000001',
                "nationalCode"  => '800008200',
                'isForeigner'   => true,
                'pervasiveCode' => '12345678901234560000'
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testItCanCreateCustomerAddressWhenMyAddressIsFalse()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/customer-addresses",
            [
                "location"     => ["lat" => 35.23211, "long" => 51.55641],
                "fullAddress"  => "Test2",
                "city"         => $this->city->getId(),
                "province"     => $this->province->getId(),
                "unit"         => 1,
                "floor"        => "1",
                "district"     => $this->district->getId(),
                "myAddress"    => 0,
                "postalCode"   => 1520960063,
                "number"       => 44,
                "name"         => 'John',
                "family"       => 'Doe',
                "mobile"       => '09011000001',
                "nationalCode" => '800008200',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('Customer address is added successfully!', $response['message']);

        $result = $response['results'];

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('fullAddress', $result);
        self::assertArrayHasKey('postalCode', $result);
        self::assertArrayHasKey('isDefault', $result);
        self::assertArrayHasKey('coordinates', $result);
        self::assertArrayHasKey('lat', $result['coordinates']);
        self::assertArrayHasKey('long', $result['coordinates']);
        self::assertArrayHasKey('number', $result);
        self::assertArrayHasKey('unit', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('family', $result);
        self::assertArrayHasKey('nationalCode', $result);
        self::assertArrayHasKey('mobile', $result);
        self::assertArrayHasKey('province', $result);
        self::assertArrayHasKey('id', $result['province']);
        self::assertArrayHasKey('code', $result['province']);
        self::assertArrayHasKey('name', $result['province']);
        self::assertArrayHasKey('city', $result);
        self::assertArrayHasKey('id', $result['city']);
        self::assertArrayHasKey('name', $result['city']);
        self::assertArrayHasKey('district', $result);
        self::assertArrayHasKey('id', $result['district']);
        self::assertArrayHasKey('name', $result['district']);
    }

    public function testValidationFailureCreateCustomerAddress()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            "/customer-addresses",
            [
                "location"    => ["lat" => 35.23211, "long" => 51.55641],
                "fullAddress" => "Test2",
                "city"        => $this->city->getId(),
                "province"    => $this->province->getId(),
                "unit"        => 1,
                "floor"       => 1,
                "district"    => $this->district->getId(),
                "myAddress"   => 0,
                "postalCode"  => 1520960063,
                "number"      => 44,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('name', $response['results']);
        self::assertArrayHasKey('family', $response['results']);
        self::assertArrayHasKey('nationalCode', $response['results']);
        self::assertArrayHasKey('mobile', $response['results']);
        self::assertArrayHasKey('metas', $response);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertContains('This value should not be blank.', $response['results']['name']);
        self::assertContains('This value should not be blank.', $response['results']['family']);
        self::assertContains('This value should not be blank.', $response['results']['nationalCode']);
        self::assertContains('This value should not be blank.', $response['results']['mobile']);
    }

    public function testItCanUpdateCustomerAddress()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PUT',
            "/customer-addresses/{$this->customerAddress->getId()}",
            [
                "location"     => ["lat" => 35.25611, "long" => 51.57341],
                "fullAddress"  => "Test-Update",
                "city"         => $this->city->getId(),
                "province"     => $this->province->getId(),
                "district"     => $this->district->getId(),
                "unit"         => 5,
                "floor"        => "۱",
                "postalCode"   => 1620960063,
                "number"       => 34,
                "name"         => 'John',
                "family"       => 'Doe',
                "mobile"       => '09011000001',
                "nationalCode" => '800008200',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertEquals('Customer address is updated successfully!', $response['message']);

        $result = $response['results'];

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('fullAddress', $result);
        self::assertArrayHasKey('postalCode', $result);
        self::assertArrayHasKey('isDefault', $result);
        self::assertArrayHasKey('coordinates', $result);
        self::assertArrayHasKey('lat', $result['coordinates']);
        self::assertArrayHasKey('long', $result['coordinates']);
        self::assertArrayHasKey('number', $result);
        self::assertArrayHasKey('unit', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('family', $result);
        self::assertArrayHasKey('nationalCode', $result);
        self::assertArrayHasKey('mobile', $result);
        self::assertArrayHasKey('province', $result);
        self::assertArrayHasKey('id', $result['province']);
        self::assertArrayHasKey('code', $result['province']);
        self::assertArrayHasKey('name', $result['province']);
        self::assertArrayHasKey('city', $result);
        self::assertArrayHasKey('id', $result['city']);
        self::assertArrayHasKey('name', $result['city']);
        self::assertArrayHasKey('district', $result);
        self::assertArrayHasKey('id', $result['district']);
        self::assertArrayHasKey('name', $result['district']);
    }

    public function testValidationFailureUpdateCustomerAddress()
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PUT',
            "/customer-addresses/{$this->customerAddress->getId()}",
            [
                "location"    => ["lat" => 35.25611, "long" => 51.57341],
                "fullAddress" => "Test-Update",
                "city"        => $this->city->getId(),
                "province"    => $this->province->getId(),
                "district"    => $this->district->getId(),
                "unit"        => 5,
                "floor"       => 5,
                "myAddress"   => 0,
                "postalCode"  => 1620960063,
                "number"      => 34,
            ]
        );

        $response = $this->getControllerResponse();

        self::assertEquals(422, $client->getResponse()->getStatusCode());
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('name', $response['results']);
        self::assertArrayHasKey('family', $response['results']);
        self::assertArrayHasKey('nationalCode', $response['results']);
        self::assertArrayHasKey('mobile', $response['results']);
        self::assertArrayHasKey('metas', $response);
        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);

        self::assertContains('This value should not be blank.', $response['results']['name']);
        self::assertContains('This value should not be blank.', $response['results']['family']);
        self::assertContains('This value should not be blank.', $response['results']['nationalCode']);
        self::assertContains('This value should not be blank.', $response['results']['mobile']);
    }

    public function testItHasNotAccessForUpdatingCustomerAddress(): void
    {
        $customerRepo    = $this->getService(CustomerRepository::class);
        $customer        = $customerRepo->findOneBy(['mobile' => '09121234569']);
        $customerAddress = $this->customerAddressRepo->findOneBy(['customer' => $customer]);

        $client = $this->loginAs($this->customer)->sendRequest(
            'PUT',
            "/customer-addresses/{$customerAddress->getId()}",
            [
                "location"    => ["lat" => 35.25611, "long" => 51.57341],
                "fullAddress" => "Test-Update",
                "city"        => $this->city->getId(),
                "province"    => $this->province->getId(),
                "district"    => $this->district->getId(),
                "unit"        => 5,
                "myAddress"   => 0,
                "postalCode"  => 1620960063,
                "number"      => 34,
            ]
        );

        self::assertEquals(403, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(403, $response['status']);
        self::assertEquals('Access Denied.', $response['detail']);
    }

    public function testItCanGetCustomerAddressesList(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest('GET', "/customer-addresses");

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        foreach ($response['results'] as $customerAddress) {
            self::assertArrayHasKey('id', $customerAddress);
            self::assertArrayHasKey('fullAddress', $customerAddress);
            self::assertArrayHasKey('postalCode', $customerAddress);
            self::assertArrayHasKey('isDefault', $customerAddress);
            self::assertArrayHasKey('coordinates', $customerAddress);
            self::assertArrayHasKey('lat', $customerAddress['coordinates']);
            self::assertArrayHasKey('long', $customerAddress['coordinates']);
            self::assertArrayHasKey('number', $customerAddress);
            self::assertArrayHasKey('unit', $customerAddress);
            self::assertArrayHasKey('name', $customerAddress);
            self::assertArrayHasKey('family', $customerAddress);
            self::assertArrayHasKey('nationalCode', $customerAddress);
            self::assertArrayHasKey('mobile', $customerAddress);
            self::assertArrayHasKey('province', $customerAddress);
            self::assertArrayHasKey('id', $customerAddress['province']);
            self::assertArrayHasKey('code', $customerAddress['province']);
            self::assertArrayHasKey('name', $customerAddress['province']);
            self::assertArrayHasKey('city', $customerAddress);
            self::assertArrayHasKey('id', $customerAddress['city']);
            self::assertArrayHasKey('name', $customerAddress['city']);
            self::assertArrayHasKey('district', $customerAddress);
            self::assertArrayHasKey('id', $customerAddress['district']);
            self::assertArrayHasKey('name', $customerAddress['district']);
        }
    }

    public function testItCanDeleteCustomerAddress()
    {
        $id     = $this->customerAddress->getId();
        $client = $this->loginAs($this->customer)->sendRequest('DELETE', "/customer-addresses/{$id}");

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('id', $response['results']);
        self::assertEquals($id, $response['results']['id']);
        self::assertArrayHasKey('metas', $response);
    }

    public function testItHasNotAccessForDeletingCustomerAddress()
    {
        $customerRepo    = $this->getService(CustomerRepository::class);
        $customer        = $customerRepo->findOneBy(['mobile' => '09121234569']);
        $customerAddress = $this->customerAddressRepo->findOneBy(['customer' => $customer]);

        $client = $this->loginAs($this->customer)
                       ->sendRequest('DELETE', "/customer-addresses/{$customerAddress->getId()}");

        self::assertEquals(403, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(403, $response['status']);
        self::assertEquals('Access Denied.', $response['detail']);
    }
}
