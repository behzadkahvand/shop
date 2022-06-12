<?php

namespace App\Tests\Controller\Seller;

use App\Tests\Controller\BaseControllerTestCase;

class AuthControllerTest extends BaseControllerTestCase
{
    public function testUpdatePasswordSuccessfully(): void
    {
        $oldPassword = $this->seller->getPassword();

        $response = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.auth.password.change'),
            [
                'oldPassword' => '123456', // default password in fixtures
                'newPassword' => 'RANDOM_STRING',
            ]
        );

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        self::assertNotEquals($oldPassword, $this->seller->getPassword());
    }

    public function testItFailsOnValidationWhenUpdatingPassword(): void
    {
        $response = $this->loginAs($this->seller)->sendRequest(
            'POST',
            $this->route('seller.auth.password.change'),
            [
                'oldPassword' => '123456', // default password in fixtures
                'newPassword' => null,
            ]
        );

        self::assertEquals(422, $response->getResponse()->getStatusCode());
    }

    public function testItCanGetSellerProfileByToken(): void
    {
        $response = $this->loginAs($this->seller)->sendRequest('GET', $this->route('seller.auth.profile'));

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $results = data_get(json_decode($response->getResponse()->getContent(), true), 'results');

        self::assertEquals($this->seller->getName(), $results['name']);
        self::assertEquals($this->seller->getUsername(), $results['username']);
        self::assertEquals($this->seller->getIsLimited(), $results['isLimited']);
        self::assertEquals($this->seller->getNationalNumber(), $results['nationalNumber']);
        self::assertEquals($this->seller->getNationalIdentifier(), $results['nationalIdentifier']);
    }
}
