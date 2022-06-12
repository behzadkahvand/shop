<?php

namespace App\Tests\Controller;

class ApiDocHealthCheckTest extends BaseControllerTestCase
{
    public function testAdminApiDocIsUpAndRunning(): void
    {
        $response = $this->sendRequest('GET', '/doc/admin');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testCustomerApiDocIsUpAndRunning(): void
    {
        $response = $this->sendRequest('GET', '/doc/customer');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testSellerApiDocIsUpAndRunning(): void
    {
        $response = $this->sendRequest('GET', '/doc/seller');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testCarrierApiDocIsUpAndRunning(): void
    {
        $response = $this->sendRequest('GET', '/doc/carrier');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }

    public function testLandingApiDocIsUpAndRunning(): void
    {
        $response = $this->sendRequest('GET', '/doc/landing');

        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }
}
