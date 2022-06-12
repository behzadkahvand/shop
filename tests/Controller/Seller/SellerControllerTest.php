<?php

namespace App\Tests\Controller\Seller;

use App\Tests\Controller\BaseControllerTestCase;

class SellerControllerTest extends BaseControllerTestCase
{
    public function testBestSellers(): void
    {
        $client = $this->loginAs($this->seller)
                       ->sendRequest(
                           'GET',
                           'seller/best-sellers'
                       );


        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        $this->assertSuccessResponseKeys();

        $sellers = $response['results'];
        self::assertNotEmpty($sellers);
        self::assertCount(10, $sellers);
        $seller = $sellers[0];
        self::assertArrayHasKeys(
            [
                "id",
                "identifier",
                "name",
                "mobile",
                "address",
                "fullName",
                "score"
            ],
            $seller
        );
    }
}
