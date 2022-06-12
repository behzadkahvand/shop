<?php

namespace App\Tests\Controller\Customer;

use App\Tests\Controller\BaseControllerTestCase;

class InventoryPriceLogControllerTest extends BaseControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testFilterPriceHistoryWhenFilterNotExist()
    {
        $response = $this->sendRequest('GET', $this->router->generate('customer.price-chart.filter-price-history'));
        self::assertEquals(200, $response->getResponse()->getStatusCode());
    }


    public function testFilterPriceHistory()
    {
        $response = $this->sendRequest('GET', $this->router->generate('customer.price-chart.filter-price-history', [
            'filter' => ['inventory_price.color.id' => 93],
        ]));

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse();

        self::assertIsArray($result['results']);

        self::assertArrayHasKey('succeed', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('results', $result);
        self::assertArrayHasKey('metas', $result);

        self::assertTrue($result['succeed']);

        if ($result['results']) {
            $result = $result['results'][0];
            self::assertArrayHasKey('priceFrom', $result);
            self::assertArrayHasKey('finalPriceFrom', $result);
            self::assertArrayHasKey('priceTo', $result);
            self::assertArrayHasKey('finalPriceTo', $result);
            self::assertArrayHasKey('sellerName', $result);
            self::assertArrayHasKey('guarantee', $result);
            self::assertArrayHasKey('color', $result);
            self::assertArrayHasKey('size', $result);
            self::assertArrayHasKey('date', $result);
        }
    }
}
