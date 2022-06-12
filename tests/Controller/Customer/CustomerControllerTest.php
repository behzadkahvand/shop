<?php

namespace App\Tests\Controller\Customer;

use App\Tests\Controller\BaseControllerTestCase;

class CustomerControllerTest extends BaseControllerTestCase
{
    public function testShow(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->router->generate('customer.customers.show')
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdate(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'name' => 'test',
                'family' => 'test',
                'email' => 'test@test.com',
                'gender' => 'MALE',
                'birthday' => '1990-12-23',
                'nationalNumber' => '0011772591',
                'mobile' => '09121234567',
                'account' => [
                    'cardNumber' => '6362141110111193',
                ],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUpdateForeignerWithEmptyNationalNumber(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'name' => 'test',
                'family' => 'test',
                'email' => 'test@test.com',
                'gender' => 'MALE',
                'birthday' => '1990-12-23',
                'isForeigner' => true,
                'nationalNumber' => '',
                'mobile' => '09121234567',
                'account' => [
                    'cardNumber' => '6362141110111193',
                ],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertTrue($this->customer->getIsForeigner());
        self::assertNull($this->customer->getNationalNumber());
        self::assertNull($this->customer->getPervasiveCode());
    }

    public function testUpdateForeignerWithPervasiveCode(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'name' => 'test',
                'family' => 'test',
                'email' => 'test@test.com',
                'gender' => 'MALE',
                'birthday' => '1990-12-23',
                'isForeigner' => true,
                'nationalNumber' => '',
                'pervasiveCode' => '1234567890123456',
                'mobile' => '09121234567',
                'account' => [
                    'cardNumber' => '6362141110111193',
                ],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertTrue($this->customer->getIsForeigner());
        self::assertNull($this->customer->getNationalNumber());
        self::assertNotNull($this->customer->getPervasiveCode());
    }

    public function testUpdateForeignerValidationErrorDueToBigLengthForPervasiveCode(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'name' => 'test',
                'family' => 'test',
                'email' => 'test@test.com',
                'gender' => 'MALE',
                'birthday' => '1990-12-23',
                'isForeigner' => true,
                'nationalNumber' => '',
                'pervasiveCode' => '1234567890123456',
                'mobile' => '09121234567',
                'account' => [
                    'cardNumber' => '63621411101111930000',
                ],
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateFailForNonForeignerWithEmptyNationalNumber(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'name' => 'test',
                'family' => 'test',
                'email' => 'test@test.com',
                'gender' => 'MALE',
                'birthday' => '1990-12-23',
                'isForeigner' => false,
                'nationalNumber' => '',
                'mobile' => '09121234567',
                'account' => [
                    'cardNumber' => '6362141110111193',
                ],
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateFails(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'name' => '',
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testUpdateFailsWhenBankingCardIsWrong(): void
    {
        $invalidBankingCardNumber = '1234567891234567';

        $response = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'account' => [
                    'cardNumber' => $invalidBankingCardNumber,
                ],
            ]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse()['results'];

        self::assertEquals(
            sprintf('The value %s is not a valid card number.', $invalidBankingCardNumber),
            $result['cardNumber'][0]
        );
    }

    public function testUpdateFailsWhenBankingCardContainsChars(): void
    {
        $invalidBankingCardNumber = '123456789123456G';

        $response = $this->loginAs($this->customer)->sendRequest(
            'PATCH',
            $this->router->generate('customer.customers.update', ['id' => $this->customer->getId()]),
            [
                'account' => [
                    'cardNumber' => $invalidBankingCardNumber,
                ],
            ]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse()['results'];

        self::assertEquals(
            sprintf('The value %s is not a valid card number.', $invalidBankingCardNumber),
            $result['cardNumber'][0]
        );
    }

    public function testVerifyCard(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.customers.verify.card'),
            [
                'cardNumber' => '6362141110111193',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRegisterCard(): void
    {
        $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.customers.verify.card'),
            [
                'cardNumber' => '6362141110111193',
            ]
        );

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.customers.register.card'),
            [
                'cardNumber' => '6362141110111193',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRegisterCardFail(): void
    {
        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.customers.register.card'),
            [
                'cardNumber' => '6362141110111193',
            ]
        );

        self::assertEquals(400, $client->getResponse()->getStatusCode());
    }
}
