<?php

namespace App\Tests\Controller\Customer;

use App\Entity\City;
use App\Entity\Province;
use App\Tests\Controller\BaseControllerTestCase;

class CustomerLegalAccountControllerTest extends BaseControllerTestCase
{
    protected ?Province $province;

    protected ?City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $provinceRepo = $this->manager->getRepository(Province::class);

        $this->province = $provinceRepo->findOneBy([]);

        $cityRepo = $this->manager->getRepository(City::class);

        $this->city = $cityRepo->findOneBy([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->province = null;
        $this->city     = null;
    }

    public function testItCanStoreCustomerLegalAccount(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PUT',
            $this->router->generate('customer.customers.legal_accounts.store'),
            [
                'province'         => $this->province->getId(),
                'city'             => $this->city->getId(),
                'organizationName' => 'Lendo cooperation',
                'economicCode'     => 100000000020000,
                'nationalId'       => '04154154544',
                'registrationId'   => '041541577545',
                'phoneNumber'      => '02166455401'
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Customer legal account is stored successfully!', $response['message']);
        self::assertNotEmpty($response['results']);
        self::assertEquals([], $response['metas']);

        $customerLegalAccount = $response['results'];
        self::assertArrayHasKey('id', $customerLegalAccount);
        self::assertArrayHasKey('organizationName', $customerLegalAccount);
        self::assertArrayHasKey('economicCode', $customerLegalAccount);
        self::assertArrayHasKey('nationalId', $customerLegalAccount);
        self::assertArrayHasKey('registrationId', $customerLegalAccount);
        self::assertArrayHasKey('province', $customerLegalAccount);
        self::assertArrayHasKey('city', $customerLegalAccount);
        self::assertArrayHasKey('phoneNumber', $customerLegalAccount);

        self::assertNotEmpty($customerLegalAccount['province']);
        self::assertNotEmpty($customerLegalAccount['city']);

        self::assertArrayHasKey('id', $customerLegalAccount['province']);
        self::assertArrayHasKey('name', $customerLegalAccount['province']);
        self::assertArrayHasKey('id', $customerLegalAccount['city']);
        self::assertArrayHasKey('name', $customerLegalAccount['city']);
    }

    public function testValidationFailureStoreCustomerLegalAccount(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PUT',
            $this->router->generate('customer.customers.legal_accounts.store')
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('Validation error has been detected!', $response['message']);
        self::assertNotEmpty($response['results']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];
        self::assertArrayHasKey('province', $results);
        self::assertArrayHasKey('city', $results);
        self::assertArrayHasKey('organizationName', $results);
        self::assertArrayHasKey('economicCode', $results);
        self::assertArrayHasKey('nationalId', $results);
        self::assertArrayHasKey('registrationId', $results);
        self::assertArrayHasKey('phoneNumber', $results);

        self::assertContains('This value should not be blank.', $results['province']);
        self::assertContains('This value should not be blank.', $results['city']);
        self::assertContains('This value should not be blank.', $results['organizationName']);
        self::assertContains('This value should not be blank.', $results['economicCode']);
        self::assertContains('This value should not be blank.', $results['nationalId']);
        self::assertContains('This value should not be blank.', $results['registrationId']);
        self::assertContains('This value should not be blank.', $results['phoneNumber']);
    }

    public function testItCanShowCustomerLegalAccount(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->router->generate('customer.customers.legal_accounts.show')
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertNotEmpty($response['results']);
        self::assertEquals([], $response['metas']);

        $customerLegalAccount = $response['results'];
        self::assertArrayHasKey('id', $customerLegalAccount);
        self::assertArrayHasKey('organizationName', $customerLegalAccount);
        self::assertArrayHasKey('economicCode', $customerLegalAccount);
        self::assertArrayHasKey('nationalId', $customerLegalAccount);
        self::assertArrayHasKey('registrationId', $customerLegalAccount);
        self::assertArrayHasKey('province', $customerLegalAccount);
        self::assertArrayHasKey('city', $customerLegalAccount);
        self::assertArrayHasKey('phoneNumber', $customerLegalAccount);

        self::assertNotEmpty($customerLegalAccount['province']);
        self::assertNotEmpty($customerLegalAccount['city']);

        self::assertArrayHasKey('id', $customerLegalAccount['province']);
        self::assertArrayHasKey('name', $customerLegalAccount['province']);
        self::assertArrayHasKey('id', $customerLegalAccount['city']);
        self::assertArrayHasKey('name', $customerLegalAccount['city']);
    }
}
