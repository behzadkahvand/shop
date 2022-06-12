<?php

namespace App\Tests\Controller\Customer;

use App\Tests\Controller\BaseControllerTestCase;

class CategoryControllerTest extends BaseControllerTestCase
{
    public function testShowRoots()
    {
        $client = $this->sendRequest(
            'GET',
            $this->route('customer.categories.show.roots')
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
